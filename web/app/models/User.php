<?php
/**
 * User model.
 */
class User extends Model
{
    protected string $table = 'users';

    /**
     * Find a user by username or email.
     */
    public function findByLogin(string $identifier): ?array
    {
        return $this->db->fetch(
            'SELECT u.*, r.name AS role_name, r.slug AS role_slug
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE (u.username = ? OR u.email = ?) LIMIT 1',
            [$identifier, $identifier]
        );
    }

    /**
     * List users with role names.
     */
    public function listAll(?string $search = null): array
    {
        $sql = 'SELECT u.*, r.name AS role_name, r.slug AS role_slug
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id';
        $params = [];

        if ($search) {
            $sql .= ' WHERE u.first_name LIKE ? OR u.last_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?';
            $like = '%' . $search . '%';
            $params = [$like, $like, $like, $like];
        }

        $sql .= ' ORDER BY u.id DESC';
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Return a username that is not yet taken, starting from $base and
     * appending a number when the base is already used.
     */
    public function uniqueUsername(string $base = ''): string
    {
        $base = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9._-]/', '', $base)));
        if ($base === '') {
            $base = 'user';
        }
        $username = $base;
        $n = 1;
        while ($this->firstWhere('username', $username)) {
            $username = $base . ++$n;
        }
        return $username;
    }
}
