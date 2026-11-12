<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Exam Result';
$genderLabel = static fn($g) => match (strtolower((string) $g)) {
    'male'   => 'Male',
    'female' => 'Female',
    'other'  => 'Other',
    default  => '—',
};

$totalResults = 0;
foreach ($profiles as $p) { $totalResults += count($p['subjects']); }

$profilesWithResults = array_filter($profiles, static fn($p) => !empty($p['subjects']));
?>
<style>
    .exam-hero { background: linear-gradient(135deg, #2563EB 0%, #4F46E5 50%, #7C3AED 100%); border-radius: 18px; }
    .exam-hero .deco { position: absolute; color: #fff; pointer-events: none; user-select: none; }
    .exam-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center;
                 background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.35); color: #fff; font-size: 1.5rem; }
    .exam-stat { background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.25); border-radius: 12px;
                 padding: .5rem 1rem; color: #fff; }
    .exam-table thead th { background: #EEF2FF; color: #4338CA; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; }
    .exam-table tbody td { vertical-align: middle; }
    .exam-marks { display: inline-block; min-width: 3rem; padding: .2rem .6rem; border-radius: 8px;
                  background: #EEF2FF; color: #3730A3; font-weight: 700; text-align: center; }
    @media print {
        body * { visibility: hidden; }
        #printArea, #printArea * { visibility: visible; }
        #printArea { position: absolute; left: 0; top: 0; width: 100%; }
    }
</style>

<div class="exam-hero position-relative overflow-hidden mb-4 p-4 p-md-5">
    <i class="fas fa-graduation-cap deco" style="right:4%; bottom:-18%; font-size:8rem; opacity:.07;"></i>
    <i class="fas fa-book-open deco" style="right:20%; top:8%; font-size:5rem; opacity:.08;"></i>
    <i class="fas fa-pencil-alt deco" style="left:2%; top:-30%; font-size:7rem; opacity:.05;"></i>
    <i class="fas fa-school deco" style="left:14%; bottom:-30%; font-size:6rem; opacity:.06;"></i>
    <div class="position-relative d-flex flex-wrap align-items-center gap-3">
        <div class="exam-icon flex-shrink-0"><i class="fas fa-clipboard-list"></i></div>
        <div class="me-auto" style="min-width:220px;">
            <h4 class="text-white fw-bold mb-1">Exam Results</h4>
            <p class="text-white mb-0" style="opacity:.85;">View and manage student exam marks in one place.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="exam-stat"><i class="fas fa-users me-2"></i><?= count($profiles) ?> Students</span>
            <span class="exam-stat"><i class="fas fa-clipboard-check me-2"></i><?= $totalResults ?> Results</span>
        </div>
        <div class="d-flex gap-2">
            <button id="toggleResults" class="btn btn-light fw-semibold" data-bs-toggle="collapse" data-bs-target="#resultsPanel" aria-expanded="false" aria-controls="resultsPanel">
                <i class="fas fa-eye me-1"></i>View Results
            </button>
            <button class="btn btn-outline-light fw-semibold" data-bs-toggle="modal" data-bs-target="#addResultModal">
                <i class="fas fa-plus me-1"></i>Add Result
            </button>
        </div>
    </div>
</div>

<div class="collapse" id="resultsPanel">
    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px; overflow:hidden;">
        <div class="card-header bg-white border-bottom-0 pt-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="position-relative">
                        <i class="fas fa-search position-absolute text-muted" style="top:50%; left:.8rem; transform:translateY(-50%);"></i>
                        <input type="text" id="resultSearch" class="form-control" style="padding-left:2.2rem;" placeholder="Search student by name..." autocomplete="off">
                    </div>
                </div>
                <div class="col-12 col-md-3 col-lg-2">
                    <select id="subjectFilter" class="form-select">
                        <option value="">All Subjects</option>
                        <?php foreach ($subjects as $su): ?>
                            <option value="<?= (int) $su['id'] ?>"><?= e($su['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-auto ms-md-auto d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="window.print()"><i class="fas fa-file-pdf me-1"></i>Export PDF</button>
                </div>
            </div>
        </div>

        <div id="printArea">
            <h3 class="d-none d-print-block text-center mb-3">Exam Results</h3>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 exam-table" id="resultsTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:100px;">Official ID</th>
                            <th style="min-width:200px;">Student Name</th>
                            <th>Subject</th>
                            <th class="text-center">Marks</th>
                            <th class="text-center">Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($profilesWithResults)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No results recorded yet. Use Add Result to add a student's first result.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($profilesWithResults as $p): ?>
                            <?php $st = $p['student']; $items = $p['subjects']; $count = count($items); ?>
                            <?php $studentTotal = rtrim(rtrim(number_format(array_sum(array_map('floatval', array_column($items, 'marks_obtained'))), 2), '0'), '.'); ?>
                            <?php foreach ($items as $i => $r): ?>
                                <tr data-group="<?= (int) $st['id'] ?>" data-student="<?= e(strtolower(studentName($st))) ?>" data-subject-id="<?= (int) $r['subject_id'] ?>">
                                    <?php if ($i === 0): ?>
                                        <td class="cell-id lead-cell text-center fw-bold" rowspan="<?= $count + 1 ?>"><?= e($st['official_id'] ?? '—') ?></td>
                                        <td class="cell-name lead-cell" rowspan="<?= $count + 1 ?>">
                                            <span class="fw-semibold"><?= e(studentName($st)) ?></span>
                                            <div class="small text-muted">Gender: <?= e($genderLabel($st['gender'] ?? null)) ?></div>
                                        </td>
                                    <?php else: ?>
                                        <td class="cell-id lead-cell text-center fw-bold" style="display:none"><?= e($st['official_id'] ?? '—') ?></td>
                                        <td class="cell-name lead-cell" style="display:none">
                                            <span class="fw-semibold"><?= e(studentName($st)) ?></span>
                                            <div class="small text-muted">Gender: <?= e($genderLabel($st['gender'] ?? null)) ?></div>
                                        </td>
                                    <?php endif; ?>
                                    <td><?= e($r['subject_name']) ?></td>
                                    <td class="text-center"><span class="exam-marks"><?= e(rtrim(rtrim(number_format((float) $r['marks_obtained'], 2), '0'), '.')) ?></span></td>
                                    <td class="text-center"><?= e(formatDate($r['result_date'], 'd-m-Y')) ?></td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editResult<?= (int) $r['id'] ?>"><i class="fas fa-pen"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('grades/destroy/' . (int) $r['id']) ?>')"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="total-row" data-group="<?= (int) $st['id'] ?>">
                                <td>Total</td>
                                <td class="text-center"><span class="exam-marks"><?= e($studentTotal) ?></span></td>
                                <td colspan="2"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addResultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('grades/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Result</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Student</label>
                        <select name="student_id" id="addStudent" class="form-select" required>
                            <option value="">Select student</option>
                            <?php foreach ($profiles as $p): ?>
                                <?php $s = $p['student']; ?>
                                <option value="<?= (int) $s['id'] ?>" data-name="<?= e(studentName($s)) ?>" data-gender="<?= e($s['gender']) ?>"><?= e(studentName($s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Student Name</label>
                        <input type="text" id="addStudentName" class="form-control" placeholder="Auto from student" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Subject</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Select subject</option>
                            <?php foreach ($subjects as $su): ?>
                                <option value="<?= (int) $su['id'] ?>"><?= e($su['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Gender</label>
                        <input type="text" id="addGender" class="form-control" placeholder="Auto from student" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Date</label>
                        <input type="date" name="result_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Marks (0-100)</label>
                        <input type="number" name="marks_obtained" class="form-control" min="0" max="100" step="0.01" placeholder="0-100" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Result</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php foreach ($profiles as $p): ?>
    <?php foreach ($p['subjects'] as $r): ?>
    <div class="modal fade" id="editResult<?= (int) $r['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?= url('grades/update/' . (int) $r['id']) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="student_id" value="<?= (int) $p['student']['id'] ?>">
                    <div class="modal-header">
                        <h6 class="modal-title">Edit Subject Result</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold mb-1">Subject</label>
                            <select name="subject_id" class="form-select" required>
                                <?php foreach ($subjects as $su): ?>
                                    <option value="<?= (int) $su['id'] ?>" <?= (int) $r['subject_id'] === (int) $su['id'] ? 'selected' : '' ?>><?= e($su['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold mb-1">Date</label>
                            <input type="date" name="result_date" class="form-control" value="<?= e($r['result_date'] ?: '') ?>" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Marks (0-100)</label>
                            <input type="number" name="marks_obtained" class="form-control" value="<?= e($r['marks_obtained']) ?>" min="0" max="100" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endforeach; ?>

<script>
(function () {
    function pickStudent(sel, nameOut, genderOut) {
        const opt = sel.options[sel.selectedIndex];
        if (nameOut) nameOut.value = opt && opt.dataset.name ? opt.dataset.name : '';
        if (genderOut) genderOut.value = opt && opt.dataset.gender ? opt.dataset.gender.charAt(0).toUpperCase() + opt.dataset.gender.slice(1) : '';
    }

    const addSel = document.getElementById('addStudent');
    const addName = document.getElementById('addStudentName');
    const addGender = document.getElementById('addGender');
    if (addSel) addSel.addEventListener('change', function () { pickStudent(this, addName, addGender); });

    document.querySelectorAll('.addResultFor').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (addSel) {
                addSel.value = this.getAttribute('data-student-id');
                pickStudent(addSel, addName, addGender);
            }
        });
    });

    const toggleBtn = document.getElementById('toggleResults');
    const panel = document.getElementById('resultsPanel');
    if (toggleBtn && panel) {
        panel.addEventListener('show.bs.collapse', function () {
            toggleBtn.innerHTML = '<i class="fas fa-eye-slash me-1"></i>Hide Results';
        });
        panel.addEventListener('hidden.bs.collapse', function () {
            toggleBtn.innerHTML = '<i class="fas fa-eye me-1"></i>View Results';
        });
    }

    const searchInput = document.getElementById('resultSearch');
    const subjectFilter = document.getElementById('subjectFilter');
    const rows = document.querySelectorAll('#resultsTable tbody tr[data-group]:not(.total-row)');

    // Rebuild the rowspan grouping after any rows are hidden/shown.
    // Only the first visible row of a group keeps its ID/Name cells (with
    // rowspan covering the whole group); the other rows' ID/Name cells are
    // removed from layout (display:none) so their Subject/Marks/Date/actions
    // cells stay aligned under the same columns as the header.
    function reflow() {
        const groups = {};
        rows.forEach(function (row) {
            const g = row.getAttribute('data-group') || '';
            (groups[g] = groups[g] || []).push(row);
        });
        const totalRows = document.querySelectorAll('#resultsTable tbody tr.total-row');
        Object.keys(groups).forEach(function (g) {
            const groupRows = groups[g];
            groupRows.forEach(function (row) {
                const idC = row.querySelector('td.cell-id');
                const nameC = row.querySelector('td.cell-name');
                if (idC) { idC.style.display = 'table-cell'; idC.rowSpan = 1; }
                if (nameC) { nameC.style.display = 'table-cell'; nameC.rowSpan = 1; }
            });
            const visible = groupRows.filter(function (row) { return row.style.display !== 'none'; });
            if (visible.length === 0) return;
            let span = visible.length;
            totalRows.forEach(function (tr) {
                if (tr.getAttribute('data-group') === g && tr.style.display !== 'none') span += 1;
            });
            visible.forEach(function (row, idx) {
                const idC = row.querySelector('td.cell-id');
                const nameC = row.querySelector('td.cell-name');
                if (!idC || !nameC) return;
                if (idx === 0) {
                    idC.style.display = 'table-cell';
                    nameC.style.display = 'table-cell';
                    idC.rowSpan = Math.max(1, span);
                    nameC.rowSpan = Math.max(1, span);
                } else {
                    idC.style.display = 'none';
                    nameC.style.display = 'none';
                }
            });
        });
    }

    function applyFilters() {
        const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
        const subj = subjectFilter ? subjectFilter.value : '';
        rows.forEach(function (row) {
            const name = (row.getAttribute('data-student') || '').toLowerCase();
            const matchSearch = !q || name.indexOf(q) !== -1;
            const rowSubj = row.getAttribute('data-subject-id') || '';
            const matchSubj = !subj || rowSubj === subj;
            row.style.display = matchSearch && matchSubj ? '' : 'none';
        });
        document.querySelectorAll('#resultsTable tbody tr.total-row').forEach(function (tr) {
            const g = tr.getAttribute('data-group');
            let anyVisible = false;
            rows.forEach(function (row) {
                if (row.getAttribute('data-group') === g && row.style.display !== 'none') {
                    anyVisible = true;
                }
            });
            tr.style.display = anyVisible ? '' : 'none';
        });
        reflow();
    }
    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (subjectFilter) subjectFilter.addEventListener('change', applyFilters);
    reflow();
})();
</script>
