<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `periodic_bulletins`. */
final class PeriodicBulletin extends Entity
{
    public const TABLE = 'periodic_bulletins';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'school_id' => 'int',
        'student_id' => 'int',
        'class_id' => 'int',
        'academic_year_id' => 'int',
        'scheme_version_id' => 'int',
        'template_id' => 'string',
        'period_id' => 'string',
        'version' => 'int',
        'status' => 'string',
        'snapshot_json' => '?string',
        'created_by' => '?int',
        'validated_by' => '?int',
        'validated_at' => '?string',
        'published_by' => '?int',
        'published_at' => '?string',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['student_id', 'class_id', 'academic_year_id', 'scheme_version_id', 'period_id'];
    public const ENUMS = [
        'status' => ['draft', 'validated', 'published', 'superseded'],
    ];
    public const LENGTHS = [
        'template_id' => 120,
        'period_id' => 40,
    ];
}
