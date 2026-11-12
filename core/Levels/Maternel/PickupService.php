<?php
declare(strict_types=1);

namespace CocoCore\Levels\Maternel;

/** Sécurité des enfants : seule une personne AUTORISÉE (et active) peut reprendre l'enfant. */
final class PickupService
{
    /**
     * @param list<array{full_name:string,active:bool|int,id_document?:?string}> $authorized
     * @return array{authorized:bool,matched:?array,reason:string}
     */
    public function check(array $authorized, string $name, ?string $idDocument = null): array
    {
        $needle = $this->norm($name);
        if ($needle === '') {
            return ['authorized' => false, 'matched' => null, 'reason' => 'Nom de la personne non renseigné.'];
        }
        foreach ($authorized as $p) {
            if ($this->norm((string) $p['full_name']) !== $needle) {
                continue;
            }
            if (!(bool) $p['active']) {
                return ['authorized' => false, 'matched' => $p, 'reason' => 'Autorisation désactivée.'];
            }
            $doc = (string) ($p['id_document'] ?? '');
            if ($doc !== '' && $idDocument !== null && $this->norm($doc) !== $this->norm($idDocument)) {
                return ['authorized' => false, 'matched' => $p, 'reason' => 'Pièce d\'identité différente de celle enregistrée.'];
            }
            return ['authorized' => true, 'matched' => $p, 'reason' => 'Personne autorisée.'];
        }
        return ['authorized' => false, 'matched' => null, 'reason' => 'Personne non autorisée : prévenir la direction et les parents.'];
    }

    private function norm(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $s = strtr($s, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a', 'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ù' => 'u', 'û' => 'u', 'ç' => 'c']);
        return preg_replace('/[^a-z0-9]+/', ' ', $s) ?? '';
    }
}
