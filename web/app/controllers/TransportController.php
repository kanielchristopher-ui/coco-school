<?php
/**
 * ============================================================
 *  TransportController
 *  Vehicles, routes, drivers and student assignments.
 * ============================================================
 */

declare(strict_types=1);

class TransportController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('transport.manage');

        $this->render('transport/index', [
            'vehicles'   => (new Vehicle())->listAll(),
            'routes'     => (new Route())->all('name'),
            'drivers'    => (new Driver())->all('name'),
            'assignments'=> (new TransportAssignment())->listAll(),
            'students'   => Database::getInstance()->fetchAll('SELECT id, first_name, last_name, admission_no FROM students ORDER BY first_name'),
        ]);
    }

    // ---- Vehicles ----
    public function storeVehicle(): void
    {
        CSRF::check();
        Auth::requirePermission('transport.manage');

        $no = trim($_POST['vehicle_no'] ?? '');
        if ($no === '') {
            Flash::set('error', 'Vehicle number is required.');
            $this->redirect('transport/index');
        }

        (new Vehicle())->create([
            'vehicle_no' => $no,
            'model'      => trim($_POST['model'] ?? ''),
            'capacity'   => (int) ($_POST['capacity'] ?? 0) ?: null,
            'driver_id'  => (int) ($_POST['driver_id'] ?? 0) ?: null,
            'status'     => 'active',
        ]);
        Flash::set('success', 'Vehicle added.');
        $this->redirect('transport/index');
    }

    public function destroyVehicle(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('transport.manage');

        (new Vehicle())->delete($id);
        Flash::set('success', 'Vehicle deleted.');
        $this->redirect('transport/index');
    }

    // ---- Routes ----
    public function storeRoute(): void
    {
        CSRF::check();
        Auth::requirePermission('transport.manage');

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            Flash::set('error', 'Route name is required.');
            $this->redirect('transport/index');
        }

        (new Route())->create([
            'name'   => $name,
            'stops'  => trim($_POST['stops'] ?? ''),
            'fare'   => (float) ($_POST['fare'] ?? 0),
            'status' => 1,
        ]);
        Flash::set('success', 'Route added.');
        $this->redirect('transport/index');
    }

    public function destroyRoute(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('transport.manage');

        (new Route())->delete($id);
        Flash::set('success', 'Route deleted.');
        $this->redirect('transport/index');
    }

    // ---- Drivers ----
    public function storeDriver(): void
    {
        CSRF::check();
        Auth::requirePermission('transport.manage');

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            Flash::set('error', 'Driver name is required.');
            $this->redirect('transport/index');
        }

        (new Driver())->create([
            'name'       => $name,
            'license_no' => trim($_POST['license_no'] ?? ''),
            'phone'      => trim($_POST['phone'] ?? ''),
            'address'    => trim($_POST['address'] ?? ''),
            'status'     => 'active',
        ]);
        Flash::set('success', 'Driver added.');
        $this->redirect('transport/index');
    }

    public function destroyDriver(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('transport.manage');

        (new Driver())->delete($id);
        Flash::set('success', 'Driver deleted.');
        $this->redirect('transport/index');
    }

    // ---- Assignments ----
    public function assign(): void
    {
        CSRF::check();
        Auth::requirePermission('transport.manage');

        $studentId = (int) ($_POST['student_id'] ?? 0);
        if (!$studentId) {
            Flash::set('error', 'Please select a student.');
            $this->redirect('transport/index');
        }

        (new TransportAssignment())->create([
            'student_id'    => $studentId,
            'route_id'      => (int) ($_POST['route_id'] ?? 0) ?: null,
            'vehicle_id'    => (int) ($_POST['vehicle_id'] ?? 0) ?: null,
            'pickup_point'  => trim($_POST['pickup_point'] ?? ''),
            'amount'        => (float) ($_POST['amount'] ?? 0),
            'status'        => 'active',
        ]);
        Flash::set('success', 'Transport assigned to student.');
        $this->redirect('transport/index');
    }

    public function unassign(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('transport.manage');

        (new TransportAssignment())->delete($id);
        Flash::set('success', 'Assignment removed.');
        $this->redirect('transport/index');
    }
}
