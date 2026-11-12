<?php
// GÉNÉRÉ par tools/generate_entities.py — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain;

/** Table => classe de modèle : tous les modèles du Core. */
final class EntityRegistry
{
    public const TABLES = [
        'academic_sessions' => Entities\AcademicYear::class,
        'activity_logs' => Entities\ActivityLog::class,
        'attendance' => Entities\Attendance::class,
        'authorized_pickups' => Entities\AuthorizedPickup::class,
        'bulletin_audit_log' => Entities\BulletinAuditLog::class,
        'child_health_records' => Entities\ChildHealthRecord::class,
        'classes' => Entities\SchoolClass::class,
        'conduct_grades' => Entities\ConductGrade::class,
        'core_audit_log' => Entities\CoreAuditLog::class,
        'daily_reports' => Entities\DailyReport::class,
        'development_assessments' => Entities\DevelopmentAssessment::class,
        'development_domains' => Entities\DevelopmentDomain::class,
        'discipline_records' => Entities\DisciplineRecord::class,
        'drivers' => Entities\Driver::class,
        'establishments' => Entities\Establishment::class,
        'events' => Entities\Event::class,
        'exam_types' => Entities\ExamType::class,
        'exams' => Entities\Exam::class,
        'expense_categories' => Entities\ExpenseCategory::class,
        'expenses' => Entities\Expense::class,
        'fee_collections' => Entities\FeeCollection::class,
        'fee_discounts' => Entities\FeeDiscount::class,
        'fee_plans' => Entities\FeePlan::class,
        'fee_types' => Entities\FeeType::class,
        'fees' => Entities\Fee::class,
        'grades' => Entities\Grade::class,
        'holidays' => Entities\Holiday::class,
        'inventory_categories' => Entities\InventoryCategory::class,
        'inventory_items' => Entities\InventoryItem::class,
        'license_events' => Entities\LicenseEvent::class,
        'licenses' => Entities\LicenseRecord::class,
        'messages' => Entities\Message::class,
        'national_exam_registrations' => Entities\NationalExamRegistration::class,
        'notifications' => Entities\Notification::class,
        'official_id_counters' => Entities\OfficialIdCounter::class,
        'parents' => Entities\Guardian::class,
        'password_resets' => Entities\PasswordReset::class,
        'payments' => Entities\Payment::class,
        'payrolls' => Entities\Payroll::class,
        'periodic_bulletins' => Entities\PeriodicBulletin::class,
        'permissions' => Entities\Permission::class,
        'pickup_logs' => Entities\PickupLog::class,
        'promotions' => Entities\Promotion::class,
        'purchases' => Entities\Purchase::class,
        'remember_tokens' => Entities\RememberToken::class,
        'results' => Entities\Result::class,
        'retake_exams' => Entities\RetakeExam::class,
        'role_permissions' => Entities\RolePermission::class,
        'roles' => Entities\Role::class,
        'routes' => Entities\Route::class,
        'salary' => Entities\Salary::class,
        'school_evaluation_schemes' => Entities\SchoolEvaluationScheme::class,
        'school_extensions' => Entities\SchoolExtension::class,
        'school_options' => Entities\SchoolOption::class,
        'sections' => Entities\Section::class,
        'settings' => Entities\Setting::class,
        'staff' => Entities\Staff::class,
        'stock_issues' => Entities\StockIssue::class,
        'student_enrollments' => Entities\StudentEnrollment::class,
        'student_fees' => Entities\StudentFee::class,
        'student_marks' => Entities\StudentMark::class,
        'student_parents' => Entities\StudentParent::class,
        'students' => Entities\Student::class,
        'subjects' => Entities\Subject::class,
        'suppliers' => Entities\Supplier::class,
        'system_state' => Entities\SystemState::class,
        'teacher_attendance' => Entities\TeacherAttendance::class,
        'teachers' => Entities\Teacher::class,
        'timetables' => Entities\TimetableEntry::class,
        'transport_assignments' => Entities\TransportAssignment::class,
        'user_establishments' => Entities\UserEstablishment::class,
        'users' => Entities\User::class,
        'vehicles' => Entities\Vehicle::class,
    ];

    /** @return class-string<Entity> */
    public static function forTable(string $table): string
    {
        return self::TABLES[$table] ?? throw new \InvalidArgumentException('Table inconnue : ' . $table);
    }

    /** @return list<class-string<Entity>> */
    public static function all(): array
    {
        return array_values(self::TABLES);
    }
}
