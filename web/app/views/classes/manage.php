<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Manage Class - ' . $class['name'];
$classTeacherName = '';
foreach ($teachers as $t) {
    if ((int) $t['id'] === (int) ($class['class_teacher_id'] ?? 0)) {
        $classTeacherName = trim($t['first_name'] . ' ' . $t['last_name']);
        break;
    }
}
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <a href="<?= url('classes/index') ?>" class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-arrow-left me-1"></i>Classes</a>
        <h6 class="d-inline fw-bold mb-0"><?= e($class['name']) ?></h6>
        <?php if ($classTeacherName !== ''): ?><span class="badge bg-primary-subtle text-primary ms-2"><i class="fas fa-user-tie me-1"></i>Form Master: <?= e($classTeacherName) ?></span><?php endif; ?>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('students/index?class_id=' . $class['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-user-graduate me-1"></i>Students</a>
        <a href="<?= url('classes/edit/' . $class['id']) ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit me-1"></i>Edit</a>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3" id="classTabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-sections">Sections</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-subjects">Subjects</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-students">Students</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-timetable">Timetable</button></li>
</ul>

<div class="tab-content">
    <!-- ============ SECTIONS ============ -->
    <div class="tab-pane fade show active" id="tab-sections">
        <div class="row g-3">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Section</th><th>Room</th><th>Capacity</th><th class="text-end">Action</th></tr></thead>
                            <tbody>
                                <?php if (empty($sections)): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">No sections yet.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($sections as $sec): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= e($sec['name']) ?></td>
                                        <td><?= e($sec['room_no'] ?: '—') ?></td>
                                        <td><?= e($sec['capacity'] ?: '—') ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('classes/delete-section/' . $class['id'] . '/' . $sec['id']) ?>')"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="fw-bold mb-0">Add Section</h6></div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('classes/add-section/' . $class['id']) ?>">
                            <?= csrf_field() ?>
                            <div class="mb-2"><input type="text" name="name" class="form-control" placeholder="Section name (e.g. A)" required></div>
                            <div class="mb-2"><input type="text" name="room_no" class="form-control" placeholder="Room no (optional)"></div>
                            <div class="mb-3"><input type="number" name="capacity" class="form-control" placeholder="Capacity (optional)" min="1"></div>
                            <button class="btn btn-primary w-100 btn-sm">Add Section</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ SUBJECTS ============ -->
    <div class="tab-pane fade" id="tab-subjects">
        <div class="row g-3">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Subject</th><th>Code</th><th>Teacher</th><th>Weekly Hours</th></tr></thead>
                            <tbody>
                                <?php if (empty($subjects)): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">No subjects assigned. Add one in Subjects.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($subjects as $sub): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= e($sub['name']) ?></td>
                                        <td><?= e($sub['code'] ?: '—') ?></td>
                                        <td><?= $sub['first_name'] ? e($sub['first_name'] . ' ' . $sub['last_name']) : '—' ?></td>
                                        <td><?= $sub['credits'] !== null ? e((string) $sub['credits']) : '—' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="fw-bold mb-0">Add Subject</h6></div>
                    <div class="card-body">
                        <a href="<?= url('subjects/create') ?>" class="btn btn-primary btn-sm w-100"><i class="fas fa-plus me-1"></i>Create Subject</a>
                        <p class="text-muted small mt-2 mb-0">Subjects are created from the Subjects module and linked to this class.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ STUDENTS ============ -->
    <div class="tab-pane fade" id="tab-students">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Student</th><th>Admission No</th><th>Roll</th><th>Section</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No students in this class.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($students as $st): ?>
                            <tr>
                                <td class="d-flex align-items-center gap-2">
                                    <?= avatar($st['photo'], $st['first_name'], 30) ?>
                                    <strong><?= e($st['first_name'] . ' ' . $st['last_name']) ?></strong>
                                </td>
                                <td><?= e($st['admission_no']) ?></td>
                                <td><?= e($st['roll_no'] ?: '—') ?></td>
                                <td><?= e($st['section_name'] ?: '—') ?></td>
                                <td><?= statusBadge($st['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============ TIMETABLE ============ -->
    <div class="tab-pane fade" id="tab-timetable">
        <div class="row g-3">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Day</th><th>Period</th><th>Time</th><th>Subject</th><th>Teacher</th><th>Section</th><th class="text-end">Action</th></tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $days = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                    if (empty($timetable)): ?>
                                        <tr><td colspan="7" class="text-center text-muted py-3">No timetable entries.</td></tr>
                                    <?php endif; ?>
                                    <?php foreach ($timetable as $tt): ?>
                                        <tr>
                                            <td><?= $days[$tt['day_of_week']] ?? $tt['day_of_week'] ?></td>
                                            <td><?= e($tt['period'] ?: '—') ?></td>
                                            <td><?= e($tt['start_time'] ?: '—') ?><?= $tt['end_time'] ? ' - ' . e($tt['end_time']) : '' ?></td>
                                            <td><?= e($tt['subject_name'] ?: '—') ?></td>
                                            <td><?= $tt['teacher_first'] ? e($tt['teacher_first'] . ' ' . $tt['teacher_last']) : '—' ?></td>
                                            <td><?= e($tt['section_name'] ?: 'All') ?></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('classes/delete-timetable/' . $class['id'] . '/' . $tt['id']) ?>')"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="fw-bold mb-0">Add Timetable Entry</h6></div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('classes/store-timetable/' . $class['id']) ?>">
                            <?= csrf_field() ?>
                            <div class="mb-2">
                                <select name="day_of_week" class="form-select" required>
                                    <option value="">Select Day</option>
                                    <?php for ($d = 1; $d <= 7; $d++): ?>
                                        <option value="<?= $d ?>"><?= $days[$d] ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6"><input type="time" name="start_time" class="form-control" placeholder="Start"></div>
                                <div class="col-6"><input type="time" name="end_time" class="form-control" placeholder="End"></div>
                            </div>
                            <div class="mb-2"><input type="text" name="period" class="form-control" placeholder="Period (e.g. Period 1)"></div>
                            <div class="mb-2">
                                <select name="section_id" class="form-select">
                                    <option value="">All sections</option>
                                    <?php foreach ($sections as $sec): ?>
                                        <option value="<?= (int) $sec['id'] ?>"><?= e($sec['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <select name="subject_id" class="form-select">
                                    <option value="">Select Subject</option>
                                    <?php foreach ($subjects as $sub): ?>
                                        <option value="<?= (int) $sub['id'] ?>"><?= e($sub['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <select name="teacher_id" class="form-select">
                                    <option value="">Select Teacher</option>
                                    <?php foreach ($teachers as $t): ?>
                                        <option value="<?= (int) $t['id'] ?>"><?= e($t['first_name'] . ' ' . $t['last_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button class="btn btn-primary w-100 btn-sm">Add Entry</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
