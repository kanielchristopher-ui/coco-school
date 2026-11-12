<?php
declare(strict_types=1);

namespace CocoCore\Database;

use CocoCore\Contracts\PermissionRepositoryInterface;

final class PdoPermissionRepository implements PermissionRepositoryInterface
{
    public function __construct(private Connection $db)
    {
    }

    public function matrix(): array
    {
        $m = [];
        foreach ($this->db->fetchAll(
            'SELECT r.slug AS role, p.slug AS permission FROM role_permissions rp
             INNER JOIN roles r ON r.id = rp.role_id INNER JOIN permissions p ON p.id = rp.permission_id'
        ) as $row) {
            $m[$row['role']][] = $row['permission'];
        }
        return $m;
    }
}
