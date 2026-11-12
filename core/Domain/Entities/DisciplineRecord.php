<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `discipline_records`. */
final class DisciplineRecord extends Entity
{
    public const TABLE = 'discipline_records';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'student_id' => 'int',
        'incident_date' => 'string',
        'type' => 'string',
        'severity' => 'string',
        'description' => '?string',
        'sanction' => '?string',
        'parent_notified' => 'bool',
        'status' => 'string',
        'recorded_by' => '?int',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['student_id', 'incident_date', 'type'];
    public const ENUMS = [
        'severity' => ['minor', 'moderate', 'serious'],
        'status' => ['open', 'resolved'],
    ];
    public const LENGTHS = [
        'type' => 60,
        'sanction' => 100,
    ];
}
