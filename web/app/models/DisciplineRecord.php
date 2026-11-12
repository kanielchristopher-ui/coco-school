<?php
/**
 * DisciplineRecord model : incidents disciplinaires des élèves.
 */
class DisciplineRecord extends Model
{
    protected string $table = 'discipline_records';

    public const TYPES = [
        'Late arrival', 'Unjustified absence', 'Disrespect', 'Fight', 'Cheating',
        'Theft', 'Uniform not respected', 'Phone in class', 'Damage to property', 'Other',
    ];

    public const SANCTIONS = [
        'Warning', 'Detention', 'Parents summoned', 'Temporary exclusion', 'Permanent expulsion', 'None',
    ];
}
