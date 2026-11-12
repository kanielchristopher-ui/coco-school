<?php
/**
 * ============================================================
 *  ReportController
 *  Aggregated reports: admission, attendance, fees, exam,
 *  teachers, income and expenses.
 * ============================================================
 */

declare(strict_types=1);

class ReportController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('reports.view');
        $this->render('reports/index');
    }

    /**
     * Generic report builder driven by a report key.
     */
    public function view(string $report): void
    {
        Auth::requirePermission('reports.view');

        $db = Database::getInstance();
        $data = ['report' => $report, 'title' => ucwords(str_replace('_', ' ', $report)), 'rows' => []];

        switch ($report) {
            case 'admission':
                $data['headers'] = ['Admission No', 'Student', 'Class', 'Section', 'Gender', 'Admission Date', 'Status'];
                $data['rows'] = $db->fetchAll(
                    'SELECT s.admission_no,
                            CONCAT_WS(" ", s.first_name, s.last_name) AS student,
                            c.name AS class_name, sec.name AS section_name, s.gender, s.admission_date, s.status
                     FROM students s LEFT JOIN classes c ON c.id = s.class_id LEFT JOIN sections sec ON sec.id = s.section_id
                     ORDER BY s.admission_date DESC'
                );
                break;

            case 'attendance':
                $data['headers'] = ['Student', 'Class', 'Present', 'Late', 'Absent', 'Leave', 'Total'];
                $data['rows'] = $db->fetchAll(
                    'SELECT CONCAT_WS(" ", s.first_name, s.last_name) AS student, c.name AS class_name,
                            SUM(a.status = "present") AS present, SUM(a.status = "late") AS late,
                            SUM(a.status = "absent") AS absent, SUM(a.status = "leave") AS `leave`, COUNT(*) AS total
                     FROM attendance a INNER JOIN students s ON s.id = a.student_id LEFT JOIN classes c ON c.id = a.class_id
                     GROUP BY s.id ORDER BY s.first_name'
                );
                break;

            case 'fees':
                $data['headers'] = ['Invoice', 'Student', 'Amount', 'Discount', 'Scholarship', 'Paid', 'Status'];
                $data['rows'] = $db->fetchAll(
                    'SELECT f.invoice_no,
                            CONCAT_WS(" ", s.first_name, s.last_name) AS student,
                            f.amount, f.discount_amount, f.scholarship,
                            COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.fee_id = f.id), 0) AS paid,
                            f.status
                     FROM fees f INNER JOIN students s ON s.id = f.student_id ORDER BY f.id DESC'
                );
                break;

            case 'exam':
                $data['headers'] = ['Student', 'Roll', 'Total', 'Max', 'Percentage', 'Grade'];
                $data['rows'] = $db->fetchAll(
                    'SELECT CONCAT_WS(" ", s.first_name, s.last_name) AS student, s.roll_no,
                            ROUND(SUM(r.marks_obtained),2) AS total, ROUND(SUM(r.max_marks),2) AS max_total,
                            ROUND((SUM(r.marks_obtained)/SUM(r.max_marks))*100,2) AS percentage,
                            (SELECT grade FROM results r2 WHERE r2.student_id = r.student_id LIMIT 1) AS grade
                     FROM results r INNER JOIN students s ON s.id = r.student_id
                     GROUP BY r.student_id ORDER BY percentage DESC'
                );
                break;

            case 'teacher':
                $data['headers'] = ['Employee ID', 'Name', 'Department', 'Qualification', 'Salary', 'Status'];
                $data['rows'] = $db->fetchAll(
                    'SELECT employee_id,
                            CONCAT_WS(" ", first_name, last_name) AS name,
                            department, qualification, salary, status FROM teachers ORDER BY employee_id'
                );
                break;

            case 'income':
                $data['headers'] = ['Receipt', 'Student', 'Amount', 'Method', 'Date'];
                $data['rows'] = $db->fetchAll(
                    'SELECT p.receipt_no,
                            CONCAT_WS(" ", s.first_name, s.last_name) AS student,
                            p.amount, p.payment_method, p.payment_date
                     FROM payments p INNER JOIN students s ON s.id = p.student_id ORDER BY p.payment_date DESC'
                );
                $data['summary'] = (float) $db->fetchColumn('SELECT COALESCE(SUM(amount),0) FROM payments');
                break;

            case 'expense':
                $data['headers'] = ['Item', 'Quantity', 'Unit Price', 'Total', 'Date'];
                $data['rows'] = $db->fetchAll(
                    'SELECT i.name AS item_name, pu.quantity, pu.unit_price, pu.total, pu.purchase_date
                     FROM purchases pu INNER JOIN inventory_items i ON i.id = pu.item_id ORDER BY pu.purchase_date DESC'
                );
                $data['summary'] = (float) $db->fetchColumn('SELECT COALESCE(SUM(total),0) FROM purchases');
                break;

            default:
                $data['headers'] = [];
                $data['rows'] = [];
        }

        $this->render('reports/view', $data);
    }

    /**
     * Export a report to CSV.
     */
    public function export(string $report): void
    {
        Auth::requirePermission('reports.view');

        $db = Database::getInstance();
        $headers = [];
        $rows = [];

        switch ($report) {
            case 'admission':
                $headers = ['Admission No', 'Student', 'Class', 'Section', 'Gender', 'Admission Date', 'Status'];
                $rows = $db->fetchAll(
                    'SELECT s.admission_no,
                            CONCAT_WS(" ", s.first_name, s.last_name) AS student,
                            c.name AS class_name, sec.name AS section_name, s.gender, s.admission_date, s.status
                     FROM students s LEFT JOIN classes c ON c.id = s.class_id LEFT JOIN sections sec ON sec.id = s.section_id'
                );
                $rows = array_map(fn($r) => array_values($r), $rows);
                break;
            case 'fees':
                $headers = ['Invoice', 'Student', 'Amount', 'Status'];
                $rows = $db->fetchAll(
                    'SELECT f.invoice_no,
                            CONCAT_WS(" ", s.first_name, s.last_name) AS student,
                            f.amount, f.status
                     FROM fees f INNER JOIN students s ON s.id = f.student_id'
                );
                $rows = array_map(fn($r) => array_values($r), $rows);
                break;
            case 'attendance':
                $headers = ['Student', 'Class', 'Present', 'Late', 'Absent', 'Leave'];
                $rows = $db->fetchAll(
                    'SELECT CONCAT_WS(" ", s.first_name, s.last_name) AS student, c.name,
                            SUM(a.status = "present"), SUM(a.status = "late"), SUM(a.status = "absent"), SUM(a.status = "leave")
                     FROM attendance a INNER JOIN students s ON s.id = a.student_id LEFT JOIN classes c ON c.id = a.class_id
                     GROUP BY s.id'
                );
                $rows = array_map(fn($r) => array_values($r), $rows);
                break;
            default:
                Flash::set('error', 'Export not available for this report.');
                $this->redirect('reports/index');
        }

        Exporter::csv('report_' . $report . '_' . date('Ymd'), $headers, $rows);
    }
}
