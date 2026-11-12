<?php
/**
 * ============================================================
 *  PayrollController
 *  Staff / teacher salary generation and payslips.
 * ============================================================
 */

declare(strict_types=1);

class PayrollController extends Controller
{
    protected Salary $salary;

    public function __construct()
    {
        parent::__construct();
        $this->salary = new Salary();
    }

    public function index(): void
    {
        Auth::requirePermission('payroll.manage');

        $rows = $this->salary->listAll();
        $total = count($rows);
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $pagination = new Pagination($total, $page, PAGINATION_LIMIT);
        $rows = array_slice($rows, $pagination->offset(), PAGINATION_LIMIT);

        $this->render('payroll/index', [
            'rows'       => $rows,
            'pagination' => $pagination,
            'teachers'   => (new Teacher())->listAll(),
            'staff'      => (new Staff())->all('first_name'),
        ]);
    }

    public function store(): void
    {
        CSRF::check();
        Auth::requirePermission('payroll.manage');

        $teacherId = (int) ($_POST['teacher_id'] ?? 0);
        $staffId   = (int) ($_POST['staff_id'] ?? 0);
        $month     = (int) ($_POST['month'] ?? (int) date('n'));
        $year      = (int) ($_POST['year'] ?? (int) date('Y'));

        if (!$teacherId && !$staffId) {
            Flash::set('error', 'Select an employee.');
            $this->redirect('payroll/index');
        }

        // Fetch base salary.
        if ($teacherId) {
            $t = (new Teacher())->find($teacherId);
            $basic = (float) $t['salary'];
        } else {
            $s = (new Staff())->find($staffId);
            $basic = (float) $s['basic_salary'];
        }

        $allowances = (float) ($_POST['allowances'] ?? 0);
        $deductions = (float) ($_POST['deductions'] ?? 0);
        $net = max(0, $basic + $allowances - $deductions);

        $this->salary->create([
            'staff_id'     => $staffId ?: null,
            'teacher_id'   => $teacherId ?: null,
            'month'        => $month,
            'year'         => $year,
            'basic_salary' => $basic,
            'allowances'   => $allowances,
            'deductions'   => $deductions,
            'net_salary'   => $net,
            'payment_date' => date('Y-m-d'),
            'status'       => 'paid',
        ]);

        $this->logActivity('create', 'payroll', 'Generated salary for ' . $year . '-' . $month);
        Flash::set('success', 'Salary generated successfully.');
        $this->redirect('payroll/index');
    }

    /**
     * Printable payslip.
     */
    public function payslip(int $id): void
    {
        Auth::requirePermission('payroll.manage');

        $record = Database::getInstance()->fetch(
            'SELECT sal.*,
                    COALESCE(t.first_name, st.first_name) AS first_name,
                    COALESCE(t.last_name, st.last_name) AS last_name,
                    COALESCE(t.employee_id, st.employee_id) AS employee_id,
                    COALESCE(t.department, st.department) AS department
             FROM salary sal
             LEFT JOIN teachers t ON t.id = sal.teacher_id
             LEFT JOIN staff st ON st.id = sal.staff_id
             WHERE sal.id = ?',
            [$id]
        );

        if (!$record) {
            $this->renderError('Payslip not found.');
            return;
        }

        $this->renderRaw('payroll/payslip', ['record' => $record]);
    }

    public function destroy(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('payroll.manage');

        $this->salary->delete($id);
        Flash::set('success', 'Salary record deleted.');
        $this->redirect('payroll/index');
    }

    protected function renderError(string $message): void
    {
        http_response_code(404);
        $this->render('errors/404', ['message' => $message]);
    }
}
