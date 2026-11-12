<?php
/**
 * ============================================================
 *  StaffController
 *  Non-teaching staff records.
 * ============================================================
 */

declare(strict_types=1);

class StaffController extends Controller
{
    /**
     * List staff with search, filter and pagination.
     */
    public function index(): void
    {
        Auth::requirePermission('staff.manage');

        $search = trim($_GET['q'] ?? '');
        $status = trim($_GET['status'] ?? '');

        $sql = 'SELECT * FROM staff WHERE 1 = 1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (first_name LIKE ? OR last_name LIKE ? OR employee_id LIKE ? OR email LIKE ?)';
            $like = '%' . $search . '%';
            array_push($params, $like, $like, $like, $like);
        }
        if (in_array($status, ['active', 'inactive', 'left'], true)) {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY first_name';

        $rows = Database::getInstance()->fetchAll($sql, $params);
        $total = count($rows);
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $pagination = new Pagination($total, $page, PAGINATION_LIMIT);
        $rows = array_slice($rows, $pagination->offset(), PAGINATION_LIMIT);

        $this->render('staff/index', [
            'rows'       => $rows,
            'pagination' => $pagination,
            'total'      => $total,
            'search'     => $search,
            'status'     => $status,
        ]);
    }

    /**
     * Show the add-staff form.
     */
    public function create(): void
    {
        Auth::requirePermission('staff.manage');
        $this->render('staff/create');
    }

    /**
     * Add a staff member.
     */
    public function store(): void
    {
        CSRF::check();
        Auth::requirePermission('staff.manage');

        $first = trim($_POST['first_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($first === '' || $email === '') {
            Flash::set('error', 'First name and email are required.');
            $this->redirect('staff/create');
        }

        $employeeId = trim($_POST['employee_id'] ?? '');
        if ($employeeId === '') {
            $employeeId = 'STF' . str_pad((string) ((new Staff())->count() + 1), 4, '0', STR_PAD_LEFT);
        }

        $id = (new Staff())->create([
            'employee_id'  => $employeeId,
            'first_name'   => $first,
            'last_name'    => trim($_POST['last_name'] ?? ''),
            'department'   => trim($_POST['department'] ?? ''),
            'designation'  => trim($_POST['designation'] ?? ''),
            'phone'        => trim($_POST['phone'] ?? ''),
            'email'        => $email,
            'joining_date' => trim($_POST['joining_date'] ?? '') ?: null,
            'basic_salary' => (float) ($_POST['basic_salary'] ?? 0),
            'status'       => in_array(($_POST['status'] ?? 'active'), ['active', 'inactive', 'left'], true) ? $_POST['status'] : 'active',
        ]);

        $this->logActivity('create', 'staff', 'Added staff member #' . $id);
        Flash::set('success', 'Staff member added.');
        $this->redirect('staff/index');
    }

    /**
     * Show the edit-staff form.
     */
    public function edit(int $id): void
    {
        Auth::requirePermission('staff.manage');

        $staff = (new Staff())->find($id);
        if (!$staff) {
            Flash::set('error', 'Staff record not found.');
            $this->redirect('staff/index');
        }

        $this->render('staff/edit', ['staff' => $staff]);
    }

    /**
     * Update a staff member.
     */
    public function update(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('staff.manage');

        $staff = (new Staff())->find($id);
        if (!$staff) {
            Flash::set('error', 'Staff record not found.');
            $this->redirect('staff/index');
        }

        $first = trim($_POST['first_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($first === '' || $email === '') {
            Flash::set('error', 'First name and email are required.');
            $this->redirect('staff/edit/' . $id);
        }

        (new Staff())->update($id, [
            'employee_id'  => trim($_POST['employee_id'] ?? $staff['employee_id']),
            'first_name'   => $first,
            'last_name'    => trim($_POST['last_name'] ?? ''),
            'department'   => trim($_POST['department'] ?? ''),
            'designation'  => trim($_POST['designation'] ?? ''),
            'phone'        => trim($_POST['phone'] ?? ''),
            'email'        => $email,
            'joining_date' => trim($_POST['joining_date'] ?? '') ?: null,
            'basic_salary' => (float) ($_POST['basic_salary'] ?? 0),
            'status'       => in_array(($_POST['status'] ?? 'active'), ['active', 'inactive', 'left'], true) ? $_POST['status'] : 'active',
        ]);

        $this->logActivity('update', 'staff', 'Updated staff member #' . $id);
        Flash::set('success', 'Staff record updated.');
        $this->redirect('staff/index');
    }

    /**
     * Delete a staff member.
     */
    public function delete(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('staff.manage');

        (new Staff())->delete($id);
        $this->logActivity('delete', 'staff', 'Deleted staff member #' . $id);
        Flash::set('success', 'Staff record deleted.');
        $this->redirect('staff/index');
    }
}
