<?php
declare(strict_types=1);

namespace CocoCore\Database;

use CocoCore\Contracts\LicenseStoreInterface;
use CocoCore\Licensing\License;

/** Stockage des licences (tables licenses, system_state, license_events). */
final class PdoLicenseStore implements LicenseStoreInterface
{
    public function __construct(private Connection $db)
    {
    }

    public function activeToken(): ?string
    {
        $t = $this->db->fetchValue("SELECT token FROM licenses WHERE status = 'active' ORDER BY id DESC LIMIT 1");
        return $t === null ? null : (string) $t;
    }

    public function saveLicense(string $token, License $license, ?int $userId): void
    {
        $this->db->transaction(function () use ($token, $license, $userId): void {
            $this->db->run("UPDATE licenses SET status = 'replaced' WHERE status = 'active'");
            $this->db->run('DELETE FROM licenses WHERE license_id = ?', [$license->id]);
            $this->db->insert('licenses', [
                'license_id' => $license->id, 'token' => $token, 'holder' => $license->holder,
                'valid_from' => $license->validFrom->format('Y-m-d'), 'valid_until' => $license->validUntil->format('Y-m-d'),
                'installed_by' => $userId, 'status' => 'active',
            ]);
        });
    }

    public function state(string $key): ?string
    {
        $v = $this->db->fetchValue('SELECT value_text FROM system_state WHERE key_name = ?', [$key]);
        return $v === null || $v === '' ? null : (string) $v;
    }

    public function setState(string $key, string $value): void
    {
        $this->db->run('INSERT INTO system_state (key_name, value_text) VALUES (?, ?) ON DUPLICATE KEY UPDATE value_text = VALUES(value_text)', [$key, $value]);
    }

    public function event(string $event, array $details = []): void
    {
        $this->db->insert('license_events', ['event' => $event, 'details_json' => $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null]);
    }

    public function countStudents(string $level): int
    {
        return (int) $this->db->fetchValue(
            "SELECT COUNT(*) FROM students s INNER JOIN classes c ON c.id = s.class_id WHERE c.cycle = ? AND s.status = 'active'",
            [$level]
        );
    }

    public function revokedIds(): array
    {
        $raw = $this->state('revoked_ids');
        $list = $raw ? json_decode($raw, true) : [];
        return is_array($list) ? array_values(array_map('strval', $list)) : [];
    }
}
