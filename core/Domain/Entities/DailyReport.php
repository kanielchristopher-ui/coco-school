<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `daily_reports`. */
final class DailyReport extends Entity
{
    public const TABLE = 'daily_reports';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'class_id' => 'int',
        'section_id' => '?int',
        'report_date' => 'string',
        'activities' => '?string',
        'meals' => '?string',
        'nap_minutes' => '?int',
        'notes_for_parents' => '?string',
        'created_by' => '?int',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['class_id', 'report_date'];
    public const ENUMS = [];
    public const LENGTHS = [
        'meals' => 255,
    ];
}
