<?php
declare(strict_types=1);

namespace CocoCore\Database;

use CocoCore\Contracts\UserRepositoryInterface;

final class PdoUserRepository implements UserRepositoryInterface
{
    public function __construct(private Connection $db)
    {
    }

    public function findByLogin(string $login): ?array
    {
        return $this->db->fetch(
            'SELECT u.id, u.password, u.status, u.first_name, u.last_name, r.slug AS role_slug
             FROM users u INNER JOIN roles r ON r.id = u.role_id
             WHERE u.username = ? OR u.email = ? LIMIT 1',
            [$login, $login]
        );
    }

    public function updatePasswordHash(int $userId, string $hash): void
    {
        $this->db->run('UPDATE users SET password = ? WHERE id = ?', [$hash, $userId]);
    }

    public function establishmentIds(int $userId): array
    {
        return array_map('intval', array_column($this->db->fetchAll('SELECT establishment_id FROM user_establishments WHERE user_id = ?', [$userId]), 'establishment_id'));
    }
}
