<?php
declare(strict_types=1);

namespace CocoCore\Database;

/**
 * Migrations SQL versionnées : database/migrations/0001_nom.sql, 0002_nom.sql…
 * Chaque fichier est appliqué une seule fois (table schema_migrations, empreinte SHA-256).
 * Une migration déjà appliquée dont le fichier a changé est REFUSÉE (pas de modification silencieuse).
 */
final class Migrator
{
    public function __construct(private Connection $db, private string $directory)
    {
    }

    /** @return array<string,string> version => chemin, triés par version */
    public static function discover(string $directory): array
    {
        $found = [];
        foreach (glob(rtrim($directory, '/\\') . '/*.sql') ?: [] as $file) {
            if (preg_match('/^(\d{4})_[a-z0-9_]+\.sql$/', basename($file), $m)) {
                $found[$m[1]] = $file;
            }
        }
        ksort($found);
        return $found;
    }

    public function ensureTable(): void
    {
        $this->db->run('CREATE TABLE IF NOT EXISTS schema_migrations (
            version CHAR(4) NOT NULL PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            checksum CHAR(64) NOT NULL,
            applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }

    /** @return list<array{version:string,name:string,status:string}> */
    public function status(): array
    {
        $this->ensureTable();
        $applied = [];
        foreach ($this->db->fetchAll('SELECT version, checksum FROM schema_migrations') as $r) {
            $applied[$r['version']] = $r['checksum'];
        }
        $out = [];
        foreach (self::discover($this->directory) as $v => $file) {
            $sum = hash_file('sha256', $file);
            $out[] = ['version' => $v, 'name' => basename($file),
                'status' => !isset($applied[$v]) ? 'pending' : ($applied[$v] === $sum ? 'applied' : 'modified')];
        }
        return $out;
    }

    /** @return list<string> migrations appliquées pendant cet appel */
    public function migrate(): array
    {
        $done = [];
        foreach ($this->status() as $m) {
            if ($m['status'] === 'modified') {
                throw new \RuntimeException('Migration déjà appliquée puis modifiée : ' . $m['name'] . ' (créez une nouvelle migration).');
            }
            if ($m['status'] !== 'pending') {
                continue;
            }
            $file = $this->directory . '/' . $m['name'];
            // Le DDL MySQL valide implicitement : pas de transaction autour ; l'enregistrement se fait après succès.
            $this->db->pdo()->exec((string) file_get_contents($file));
            $this->db->run('INSERT INTO schema_migrations (version, name, checksum) VALUES (?, ?, ?)', [$m['version'], $m['name'], hash_file('sha256', $file)]);
            $done[] = $m['name'];
        }
        return $done;
    }
}
