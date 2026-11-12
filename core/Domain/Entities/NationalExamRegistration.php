<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `national_exam_registrations`. */
final class NationalExamRegistration extends Entity
{
    public const TABLE = 'national_exam_registrations';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'student_id' => 'int',
        'exam_code' => 'string',
        'academic_year_id' => 'int',
        'candidate_number' => '?string',
        'center' => '?string',
        'option_id' => '?int',
        'school_percent' => '?float',
        'exam_percent' => '?float',
        'final_percent' => '?float',
        'result' => 'string',
        'mention' => '?string',
        'registered_on' => 'string',
    ];
    public const REQUIRED = ['student_id', 'exam_code', 'academic_year_id', 'registered_on'];
    public const ENUMS = [
        'exam_code' => ['TENAFEP', 'EXETAT'],
        'result' => ['pending', 'passed', 'failed', 'absent'],
    ];
    public const LENGTHS = [
        'candidate_number' => 40,
        'center' => 120,
        'mention' => 40,
    ];
}
