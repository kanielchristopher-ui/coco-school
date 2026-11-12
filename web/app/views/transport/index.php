<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Transport Management';
?>
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabVehicles" role="tab"><i class="fas fa-bus me-1"></i>Vehicles (<?= count($vehicles) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabRoutes" role="tab"><i class="fas fa-route me-1"></i>Routes (<?= count($routes) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabDrivers" role="tab"><i class="fas fa-user-tie me-1"></i>Drivers (<?= count($drivers) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabAssignments" role="tab"><i class="fas fa-user-check me-1"></i>Assignments (<?= count($assignments) ?>)</a></li>
</ul>

<div class="tab-content">
    <!-- Vehicles -->
    <div class="tab-pane fade show active" id="tabVehicles" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Vehicle No</th><th>Model</th><th>Capacity</th><th>Status</th><th>Driver</th><th class="text-end">Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($vehicles)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">No vehicles added.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($vehicles as $v): ?>
                                    <tr>
                                        <td><strong><?= e($v['vehicle_no']) ?></strong></td>
                                        <td><?= e($v['model'] ?: '—') ?></td>
                                        <td><?= (int) $v['capacity'] ?></td>
                                        <td><?= statusBadge($v['status']) ?></td>
                                        <td><?= $v['driver_name'] ? e($v['driver_name']) : '<span class="text-muted">Not assigned</span>' ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('transport/destroy-vehicle/' . $v['id']) ?>')"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-plus me-2"></i>Add Vehicle</h6></div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('transport/store-vehicle') ?>">
                            <?= csrf_field() ?>
                            <div class="mb-2">
                                <label class="form-label required">Vehicle Number</label>
                                <input type="text" name="vehicle_no" class="form-control" placeholder="e.g. ABC-1234" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Model</label>
                                <input type="text" name="model" class="form-control">
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label required">Capacity</label>
                                    <input type="number" name="capacity" class="form-control" min="1" value="40" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <?php foreach (['active', 'maintenance', 'inactive'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $s === 'active' ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Driver</label>
                                <select name="driver_id" class="form-select">
                                    <option value="">Select Driver</option>
                                    <?php foreach ($drivers as $d): ?>
                                        <option value="<?= (int) $d['id'] ?>"><?= e($d['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Add Vehicle</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Routes -->
    <div class="tab-pane fade" id="tabRoutes" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Route Name</th><th>Start Point</th><th>End Point</th><th>Fare</th><th>Vehicle</th><th>Driver</th><th class="text-end">Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($routes)): ?>
                                    <tr><td colspan="7" class="text-center text-muted py-4">No routes created.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($routes as $r): ?>
                                    <tr>
                                        <td><strong><?= e($r['name']) ?></strong></td>
                                        <td><?= e($r['start_point'] ?: '—') ?></td>
                                        <td><?= e($r['end_point'] ?: '—') ?></td>
                                        <td><?= currency($r['fare']) ?></td>
                                        <td><?= !empty($r['vehicle_no']) ? e($r['vehicle_no']) : '<span class="text-muted">—</span>' ?></td>
                                        <td><?= !empty($r['driver_name']) ? e($r['driver_name']) : '<span class="text-muted">—</span>' ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('transport/destroy-route/' . $r['id']) ?>')"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-plus me-2"></i>Add Route</h6></div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('transport/store-route') ?>">
                            <?= csrf_field() ?>
                            <div class="mb-2">
                                <label class="form-label required">Route Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. North Route" required>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label">Start Point</label>
                                    <input type="text" name="start_point" class="form-control">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">End Point</label>
                                    <input type="text" name="end_point" class="form-control">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Fare</label>
                                <input type="number" name="fare" class="form-control" min="0" step="0.01" value="0">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Vehicle</label>
                                <select name="vehicle_id" class="form-select">
                                    <option value="">Select Vehicle</option>
                                    <?php foreach ($vehicles as $v): ?>
                                        <option value="<?= (int) $v['id'] ?>"><?= e($v['vehicle_no']) ?> (<?= e($v['model'] ?: 'No model') ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Driver</label>
                                <select name="driver_id" class="form-select">
                                    <option value="">Select Driver</option>
                                    <?php foreach ($drivers as $d): ?>
                                        <option value="<?= (int) $d['id'] ?>"><?= e($d['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Add Route</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Drivers -->
    <div class="tab-pane fade" id="tabDrivers" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Driver</th><th>Phone</th><th>License No</th><th>Status</th><th class="text-end">Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($drivers)): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">No drivers added.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($drivers as $d): ?>
                                    <tr>
                                        <td><strong><?= e($d['name']) ?></strong></td>
                                        <td><?= e($d['phone'] ?: '—') ?></td>
                                        <td><?= e($d['license_no'] ?: '—') ?></td>
                                        <td><?= statusBadge($d['status'] ?? 'active') ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('transport/destroy-driver/' . $d['id']) ?>')"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-plus me-2"></i>Add Driver</h6></div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('transport/store-driver') ?>">
                            <?= csrf_field() ?>
                            <div class="mb-2">
                                <label class="form-label required">Driver Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">License No</label>
                                <input type="text" name="license_no" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <?php foreach (['active', 'inactive'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $s === 'active' ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Add Driver</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignments -->
    <div class="tab-pane fade" id="tabAssignments" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Student</th><th>Admission No</th><th>Route</th><th>Vehicle</th><th>Status</th><th class="text-end">Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($assignments)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">No route assignments.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($assignments as $a): ?>
                                    <tr>
                                        <td><strong><?= e(trim(($a['student_first'] ?? $a['first_name'] ?? '') . ' ' . ($a['student_last'] ?? $a['last_name'] ?? ''))) ?></strong></td>
                                        <td><?= e($a['admission_no'] ?? '—') ?></td>
                                        <td><?= e($a['route_name'] ?? '—') ?></td>
                                        <td><?= e($a['vehicle_no'] ?? '—') ?></td>
                                        <td><?= statusBadge($a['status'] ?? 'active') ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('transport/unassign/' . $a['id']) ?>')"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-user-check me-2"></i>Assign Student</h6></div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('transport/assign') ?>">
                            <?= csrf_field() ?>
                            <div class="mb-2">
                                <label class="form-label required">Student</label>
                                <select name="student_id" class="form-select" required>
                                    <option value="">Select Student</option>
                                    <?php foreach ($students as $s): ?>
                                        <option value="<?= (int) $s['id'] ?>"><?= e($s['first_name'] . ' ' . $s['last_name']) ?> (<?= e($s['admission_no']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label required">Route</label>
                                <select name="route_id" class="form-select" required>
                                    <option value="">Select Route</option>
                                    <?php foreach ($routes as $r): ?>
                                        <option value="<?= (int) $r['id'] ?>"><?= e($r['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <?php foreach (['active', 'inactive'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $s === 'active' ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Assign Route</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
