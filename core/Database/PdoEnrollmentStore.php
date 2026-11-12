<?php
declare(strict_types=1);

namespace CocoCore\Database;

use CocoCore\Contracts\EnrollmentStoreInterface;

final class PdoEnrollmentStore implements EnrollmentStoreInterface
{
    public function __construct(private Connection $db)
    {
    }

    public function studentInfo(int $studentId): ?array
    {
        $r = $this->db->fetch('SELECT id, dob, status FROM students WHERE id = ? FOR UPDATE', [$studentId]);
        return $r ? ['id' => (int) $r['id'], 'dob' => $r['dob'], 'status' => (string) $r['status']] : null;
    }

    public function classInfo(int $classId, ?int $sectionId = null): ?array
    {
        $r = $this->db->fetch('SELECT id, name, cycle, establishment_id FROM classes WHERE id = ?', [$classId]);
        if (!$r) {
            return null;
        }
        $cap = $sectionId ? $this->db->fetchValue('SELECT capacity FROM sections WHERE id = ? AND class_id = ?', [$sectionId, $classId]) : null;
        return ['id' => (int) $r['id'], 'name' => (string) $r['name'], 'cycle' => (string) $r['cycle'],
                'establishment_id' => $r['establishment_id'] !== null ? (int) $r['establishment_id'] : null, 'section_capacity' => $cap !== null ? (int) $cap : null];
    }

    public function enrolledCount(int $classId, int $yearId): int
    {
        return (int) $this->db->fetchValue("SELECT COUNT(*) FROM student_enrollments WHERE class_id = ? AND academic_year_id = ? AND status IN ('enrolled','repeating','retake')", [$classId, $yearId]);
    }

    public function hasEnrollment(int $studentId, int $yearId): bool
    {
        return (int) $this->db->fetchValue('SELECT COUNT(*) FROM student_enrollments WHERE student_id = ? AND academic_year_id = ?', [$studentId, $yearId]) > 0;
    }

    public function insertEnrollment(array $row): int
    {
        return $this->db->insert('student_enrollments', $row);
    }

    public function setStudentClass(int $studentId, int $classId, ?int $sectionId): void
    {
        $this->db->run('UPDATE students SET class_id = ?, section_id = ? WHERE id = ?', [$classId, $sectionId, $studentId]);
    }
}
