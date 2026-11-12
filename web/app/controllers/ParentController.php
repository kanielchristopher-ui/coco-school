<?php
/**
 * ============================================================
 *  ParentController
 *  Parent portal: children, attendance, results, fees, messages.
 * ============================================================
 */

declare(strict_types=1);

class ParentController extends Controller
{
    /**
     * Only users with the parent role may use this portal.
     */
    public function checkAuth(): bool
    {
        Auth::requireLogin();
        CSRF::check();

        if (!Auth::gate(['parent'])) {
            http_response_code(403);
            $view = new View();
            $view->render('errors/403', ['message' => 'This area is for parent accounts only.'], 'layouts/app_layout');
            return false;
        }
        return true;
    }

    public function dashboard(): void
    {
        $children = (new Student())->forParent(Auth::id());
        $db = Database::getInstance();

        $childData = [];
        foreach ($children as $child) {
            $childData[] = [
                'student'  => $child,
                'fees'     => $db->fetchAll('SELECT f.*, ft.name AS fee_type_name FROM fees f LEFT JOIN fee_types ft ON ft.id = f.fee_type_id WHERE f.student_id = ?', [$child['id']]),
                'attendance' => $db->fetchAll('SELECT status, COUNT(*) AS total FROM attendance WHERE student_id = ? GROUP BY status', [$child['id']]),
                'results'  => $db->fetchAll(
                    'SELECT r.exam_id, e.name AS exam_name, et.name AS type_name, e.term,
                            ROUND(SUM(r.marks_obtained),2) AS total, ROUND(SUM(r.max_marks),2) AS max_total,
                            ROUND((SUM(r.marks_obtained)/SUM(r.max_marks))*100,2) AS percentage
                     FROM results r INNER JOIN exams e ON e.id = r.exam_id
                     LEFT JOIN exam_types et ON et.id = e.exam_type_id
                     WHERE r.student_id = ? GROUP BY r.exam_id ORDER BY e.start_date DESC',
                    [$child['id']]
                ),
            ];
        }

        $this->render('parent/dashboard', [
            'children'     => $childData,
            'parent'       => (new ParentModel())->findByUserId(Auth::id()),
            'notifications'=> (new Notification())->latest(Auth::id(), 10),
        ]);
    }

    /**
     * Marks notifications as read.
     */
    public function readNotifications(): void
    {
        CSRF::check();
        Database::getInstance()->update('notifications', ['is_read' => 1], 'user_id = :uid', [':uid' => Auth::id()]);
        $this->redirect('parent/dashboard');
    }
}
