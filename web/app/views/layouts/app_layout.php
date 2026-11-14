<?php if (!defined('APP_VERSION')) { exit; }

// Sidebar visibility helpers
$can = fn(string $p) => Auth::can($p);
$role = Auth::role();
$isParent = $role === 'parent';
$isStudent = $role === 'student';
$isTeacher = $role === 'teacher';
$isFinance = Auth::gate(['admin', 'principal', 'prefet', 'director', 'cashier']);
$isAttendanceStaff = Auth::gate(['admin', 'principal', 'prefet', 'director', 'proviseur', 'discipline']);
?>
<!DOCTYPE html>
<html lang="<?= e(Lang::locale()) ?>" dir="<?= e(Lang::dir()) ?>" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e(t($pageTitle ?? 'Dashboard')) ?> - <?= e(settings('school_short_name', 'École')) ?></title>
    <?php
    $favicon = settings('school_favicon');
    $defaultFavicon = "data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2064%2064'%3E%3Crect%20width='64'%20height='64'%20rx='14'%20fill='%234f46e5'/%3E%3Cpath%20d='M32%2015L8%2025l24%2010%2024-10z'%20fill='white'/%3E%3Cpath%20d='M19%2033v8c0%204.4%205.8%208%2013%208s13-3.6%2013-8v-8L32%2038z'%20fill='white'%20opacity='0.9'/%3E%3C/svg%3E";
    $faviconHref = ($favicon && is_file(ROOT_PATH . '/public/' . ltrim($favicon, '/'))) ? BASE_URL . '/' . e(ltrim($favicon, '/')) : $defaultFavicon;
    ?>
    <link rel="icon" href="<?= $faviconHref ?>">
    <script src="<?= asset('js/theme.js') ?>"></script>
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/fontawesome/css/fontawesome.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/fontawesome/css/solid.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/fontawesome/css/regular.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/fontawesome/css/brands.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') . '?v=' . (@filemtime(ROOT_PATH . '/public/assets/css/style.css') ?: 0) ?>">
</head>
<body>
<div class="app-wrapper">

    <!-- ======================= SIDEBAR ======================= -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <?php $logo = settings('school_logo'); if ($logo && is_file(ROOT_PATH . '/public/' . $logo)): ?>
                <img src="<?= BASE_URL . '/' . e($logo) ?>" alt="Logo" class="sidebar-logo">
            <?php else: ?>
                <i class="fas fa-graduation-cap sidebar-logo-icon"></i>
            <?php endif; ?>
            <div class="sidebar-title">
                <span class="fw-bold d-block sidebar-name"><?= e(settings('school_short_name', 'School')) ?></span>
                <small class="sidebar-sub"><?= e(t(APP_NAME)) ?></small>
            </div>
            <button class="btn btn-sm btn-link text-white d-lg-none" id="sidebarClose"><i class="fas fa-times"></i></button>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">Main</div>
            <a href="<?= url('dashboard/index') ?>" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>

            <?php if ($isParent): ?>
                <div class="nav-section">Parent Portal</div>
                <a href="<?= url('parent/dashboard') ?>" class="nav-link"><i class="fas fa-users"></i><span>My Children</span></a>
                <a href="<?= url('bulletins/my') ?>" class="nav-link"><i class="fas fa-file-lines"></i><span>Report Cards</span></a>
                <a href="<?= url('messages/notifications') ?>" class="nav-link"><i class="fas fa-bell"></i><span>Notifications</span></a>
            <?php endif; ?>

            <?php if ($isStudent): ?>
                <div class="nav-section">Student Portal</div>
                <?php if ($can('view_own_results_only')): ?>
                <a href="<?= url('results/my-results') ?>" class="nav-link"><i class="fas fa-file-alt"></i><span>My Results</span></a>
                <?php endif; ?>
                <a href="<?= url('bulletins/my') ?>" class="nav-link"><i class="fas fa-file-lines"></i><span>Report Cards</span></a>
                <?php if ($can('view_own_attendance')): ?>
                <a href="<?= url('attendance/my-attendance') ?>" class="nav-link"><i class="fas fa-calendar-check"></i><span>My Attendance</span></a>
                <?php endif; ?>
                <a href="<?= url('exams/student-exams') ?>" class="nav-link"><i class="fas fa-file-signature"></i><span>My Exams</span></a>
                <a href="<?= url('finance/my-fees') ?>" class="nav-link"><i class="fas fa-money-bill-wave"></i><span>My Fees / Receipts</span></a>
                <?php if ($can('view_teacher_messages')): ?>
                <a href="<?= url('messages/inbox') ?>" class="nav-link"><i class="fas fa-envelope"></i><span>Messages</span></a>
                <?php endif; ?>
                <a href="<?= url('messages/notifications') ?>" class="nav-link"><i class="fas fa-bell"></i><span>Notifications</span></a>
            <?php endif; ?>

            <?php if ($isTeacher): ?>
                <div class="nav-section">Teaching</div>
                <?php if ($can('view_assigned_students')): ?>
                <a href="<?= url('classes/index') ?>" class="nav-link"><i class="fas fa-school"></i><span>My Classes</span></a>
                <a href="<?= url('students/index') ?>" class="nav-link"><i class="fas fa-user-graduate"></i><span>My Students</span></a>
                <a href="<?= url('subjects/index') ?>" class="nav-link"><i class="fas fa-book-open"></i><span>My Subjects</span></a>
                <?php endif; ?>
                <?php if ($can('manage_own_exams')): ?>
                <a href="<?= url('exams/index') ?>" class="nav-link"><i class="fas fa-file-signature"></i><span>My Exams</span></a>
                <?php endif; ?>
                <?php if ($can('manage_own_attendance')): ?>
                <a href="<?= url('attendance/index') ?>" class="nav-link"><i class="fas fa-clipboard-check"></i><span>Take Attendance</span></a>
                <?php endif; ?>
                <?php if ($can('create_results') || $can('edit_results')): ?>
                <a href="<?= url('results/teacher-entry') ?>" class="nav-link"><i class="fas fa-edit"></i><span>Marks Entry</span></a>
                <?php endif; ?>
                <a href="<?= url('bulletins/marks') ?>" class="nav-link"><i class="fas fa-pen-to-square"></i><span>Report Card Marks</span></a>
                <?php if ($can('publish_results')): ?>
                <a href="<?= url('results/teacher-entry') ?>" class="nav-link"><i class="fas fa-check-circle"></i><span>Review & Publish</span></a>
                <?php endif; ?>
                <?php if ($can('send_student_messages')): ?>
                <a href="<?= url('messages/inbox') ?>" class="nav-link"><i class="fas fa-envelope"></i><span>Messages</span></a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($can('students.manage')): ?>
                <div class="nav-section">Academics</div>
                <a href="<?= url('students/index') ?>" class="nav-link"><i class="fas fa-user-graduate"></i><span>Students</span></a>
            <?php endif; ?>

            <?php if ($can('teachers.manage')): ?>
                <a href="<?= url('teachers/index') ?>" class="nav-link"><i class="fas fa-chalkboard-teacher"></i><span>Teachers</span></a>
            <?php endif; ?>

            <?php if ($can('classes.manage')): ?>
                <a href="<?= url('classes/index') ?>" class="nav-link"><i class="fas fa-school"></i><span>Classes</span></a>
            <?php endif; ?>

            <?php if ($can('subjects.manage')): ?>
                <a href="<?= url('subjects/index') ?>" class="nav-link"><i class="fas fa-book-open"></i><span>Subjects</span></a>
            <?php endif; ?>

            <?php if ($can('attendance.manage') && $isAttendanceStaff): ?>
                <div class="nav-section">Operations</div>
                <a href="<?= url('attendance/index') ?>" class="nav-link"><i class="fas fa-clipboard-check"></i><span>Attendance</span></a>
                <a href="<?= url('attendance/teacher') ?>" class="nav-link"><i class="fas fa-user-check"></i><span>Teacher Attendance</span></a>
                <a href="<?= url('attendance/report') ?>" class="nav-link"><i class="fas fa-chart-line"></i><span>Attendance Reports</span></a>
            <?php endif; ?>

            <?php if ($can('discipline.manage')): ?>
                <a href="<?= url('discipline/index') ?>" class="nav-link"><i class="fas fa-gavel"></i><span>Discipline</span></a>
            <?php endif; ?>

            <?php if ($can('exams.manage')): ?>
                <a href="<?= url('exams/index') ?>" class="nav-link"><i class="fas fa-file-signature"></i><span>Exams</span></a>
            <?php endif; ?>

            <?php if ($can('grades.manage')): ?>
                <a href="<?= url('grades/index') ?>" class="nav-link"><i class="fas fa-clipboard-list"></i><span>Exam Result</span></a>
            <?php endif; ?>

            <?php if (!$isTeacher && ($can('publish_results') || $can('results.manage'))): ?>
                <a href="<?= url('results/review-queue') ?>" class="nav-link"><i class="fas fa-clipboard-check"></i><span>Review & Publish</span></a>
            <?php endif; ?>

            <?php if (Auth::gate(['super_admin', 'admin', 'director', 'prefet', 'proviseur'])): ?>
                <a href="<?= url('bulletins/index') ?>" class="nav-link"><i class="fas fa-file-lines"></i><span>Report Cards</span></a>
                <a href="<?= url('bulletins/marks') ?>" class="nav-link"><i class="fas fa-pen-to-square"></i><span>Report Card Marks</span></a>
                <a href="<?= url('bulletins/scheme') ?>" class="nav-link"><i class="fas fa-sliders"></i><span>Evaluation Scheme</span></a>
                <a href="<?= url('bulletins/extensions') ?>" class="nav-link"><i class="fas fa-puzzle-piece"></i><span>Report Card Templates</span></a>
            <?php endif; ?>

            <?php if ($can('results.manage') && !$isTeacher): ?>
                <a href="<?= url('results/promotions') ?>" class="nav-link"><i class="fas fa-arrow-up"></i><span>Promotions</span></a>
            <?php endif; ?>

            <?php if ($isFinance): ?>
                <div class="nav-section">School Finance</div>
                <a href="<?= url('finance/fees') ?>" class="nav-link"><i class="fas fa-money-check-alt"></i><span>Student Fees</span></a>
                <a href="<?= url('finance/collections') ?>" class="nav-link"><i class="fas fa-hand-holding-usd"></i><span>Fee Collection</span></a>
                <a href="<?= url('finance/receipts') ?>" class="nav-link"><i class="fas fa-file-invoice"></i><span>Payment Receipts</span></a>
                <a href="<?= url('finance/payroll') ?>" class="nav-link"><i class="fas fa-user-tie"></i><span>Staff Payroll</span></a>
                <a href="<?= url('finance/expenses') ?>" class="nav-link"><i class="fas fa-receipt"></i><span>School Expenses</span></a>
                <a href="<?= url('finance/categories') ?>" class="nav-link"><i class="fas fa-tags"></i><span>Expense Categories</span></a>
                <a href="<?= url('finance/reports') ?>" class="nav-link"><i class="fas fa-chart-bar"></i><span>Finance Reports</span></a>
            <?php endif; ?>

            <?php if ($can('transport.manage') && settings('enable_transport_module', '0') === '1'): ?>
                <a href="<?= url('transport/index') ?>" class="nav-link"><i class="fas fa-bus"></i><span>Transport</span></a>
            <?php endif; ?>

            <?php if ($can('inventory.manage')): ?>
                <a href="<?= url('inventory/index') ?>" class="nav-link"><i class="fas fa-boxes"></i><span>Inventory</span></a>
            <?php endif; ?>

            <?php if ($can('events.manage')): ?>
                <div class="nav-section">Community</div>
                <a href="<?= url('events/index') ?>" class="nav-link"><i class="fas fa-calendar-alt"></i><span>Calendar & Events</span></a>
            <?php endif; ?>

            <?php if ($can('messaging.manage')): ?>
                <a href="<?= url('messages/inbox') ?>" class="nav-link"><i class="fas fa-envelope"></i><span>Messages</span></a>
            <?php endif; ?>

            <?php if ($can('reports.view')): ?>                <div class="nav-section">Insights</div>
                <a href="<?= url('reports/index') ?>" class="nav-link"><i class="fas fa-chart-pie"></i><span>Reports</span></a>
            <?php endif; ?>

            <?php if ($can('users.manage')): ?>
                <div class="nav-section">System</div>
                <a href="<?= url('users/index') ?>" class="nav-link"><i class="fas fa-users-cog"></i><span>Users & Roles</span></a>
            <?php endif; ?>

            <?php if ($can('staff.manage')): ?>
                <a href="<?= url('staff/index') ?>" class="nav-link"><i class="fas fa-user-tie"></i><span>Staff</span></a>
            <?php endif; ?>

            <?php if ($can('settings.manage')): ?>
                <a href="<?= url('settings/index') ?>" class="nav-link"><i class="fas fa-cog"></i><span>Settings</span></a>
                <?php if (Auth::gate(['super_admin', 'admin'])): ?>
                    <a href="<?= url('license/index') ?>" class="nav-link"><i class="fas fa-key"></i><span>License</span></a>
                <?php endif; ?>
            <?php endif; ?>

            <a href="<?= url('profile/index') ?>" class="nav-link"><i class="fas fa-user"></i><span>My Profile</span></a>
            <a href="<?= url('auth/logout') ?>" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </nav>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ======================= MAIN ======================= -->
    <main class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary d-lg-none" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                <div>
                    <h5 class="mb-0 page-heading"><?= e($pageTitle ?? 'Dashboard') ?></h5>
                    <div class="page-sub d-none d-md-block"><?= e(ldate('l j F Y')) ?></div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <?php if ($can('students.manage')): ?>
                <form class="topbar-search d-none d-lg-block" method="GET" action="<?= url('students/index') ?>" role="search">
                    <i class="fas fa-search"></i><input type="search" name="q" placeholder="Search Student" aria-label="Search Student">
                </form>
                <?php endif; ?>
                <!-- Language switcher -->
                <?= lang_switcher('dropdown') ?>

                <!-- Dark mode toggle -->
                <?= theme_switcher() ?>

                <!-- Messages -->
                <?php if ($can('messaging.manage') || $can('send_student_messages') || $can('view_teacher_messages')): ?>
                <a href="<?= url('messages/inbox') ?>" class="btn btn-outline-secondary btn-sm position-relative" title="Messages">
                    <i class="fas fa-envelope"></i>
                    <?php $unreadMsg = (new Message())->unreadCount(Auth::id()); if ($unreadMsg > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $unreadMsg ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>

                <!-- Notifications -->
                <a href="<?= url('messages/notifications') ?>" class="btn btn-outline-secondary btn-sm position-relative" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <?php $unreadNotif = (new Notification())->unreadCount(Auth::id()); if ($unreadNotif > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $unreadNotif ?></span>
                    <?php endif; ?>
                </a>

                <!-- User dropdown -->
                <div class="dropdown">
                    <button class="btn dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <?php $me = (new User())->find(Auth::id()); ?>
                        <?= avatar($me['photo'] ?? null, Auth::name(), 34) ?>
                        <span class="d-none d-md-inline text-start lh-sm"><span class="d-block small fw-semibold"><?= e(Auth::name()) ?></span><span class="d-block role-chip"><?= e(role_label(Auth::role())) ?></span></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-muted">Signed in as <strong><?= e(role_label(Auth::role())) ?></strong></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= url('profile/index') ?>"><i class="fas fa-user me-2"></i>My Profile</a></li>
                        <?php if ($can('settings.manage')): ?>
                        <li><a class="dropdown-item" href="<?= url('settings/index') ?>"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= url('auth/logout') ?>"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="content p-3 p-md-4">
            <?php $licCheck = LicenseGate::check(); if ($licCheck && $licCheck->status->severity() !== 'ok' && !$licCheck->status->isLocked()): ?>
                <div class="alert alert-<?= $licCheck->status->severity() === 'danger' ? 'danger' : 'warning' ?> d-flex align-items-center gap-2 no-print">
                    <i class="fas fa-key"></i>
                    <div class="flex-grow-1"><strong><?= e(t($licCheck->status->label())) ?></strong>
                        <?php if ($licCheck->daysLeft !== null && $licCheck->daysLeft >= 0): ?> — <?= e(t(':n day(s) left', ['n' => $licCheck->daysLeft])) ?><?php endif; ?></div>
                    <?php if (in_array(Auth::role(), ['super_admin', 'admin'], true)): ?><a href="<?= url('license/index') ?>" class="btn btn-sm btn-outline-secondary"><?= e(t('Manage license')) ?></a><?php endif; ?>
                </div>
            <?php endif; ?>
            <?php flash(); ?>
            <?= $content ?? '' ?>
        </div>

        <footer class="app-footer text-center py-3 text-muted small">
            <?= e(settings('school_name', APP_NAME)) ?> &middot; <?= e(APP_NAME) ?> v<?= e(APP_VERSION) ?> &middot; &copy; <?= date('Y') ?>
        </footer>
    </main>
</div>

<!-- Confirm delete modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Confirm Action</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Are you sure you want to perform this action?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <a href="#" class="btn btn-danger btn-sm" id="confirmActionBtn">Yes, continue</a>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script>
window.CAL = <?= json_encode(['months' => (array) Lang::meta('months', []) ?: null, 'days_short' => (array) Lang::meta('days_short', []) ?: null, 'first_day' => 1], JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="<?= asset('js/local-ui.js') ?>"></script>
<script>
window.I18N = <?= json_encode(Lang::jsStrings(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.tr = function (s) { return (window.I18N && window.I18N[s]) || s; };
</script>
<script src="<?= asset('js/main.js') . '?v=' . (@filemtime(ROOT_PATH . '/public/assets/js/main.js') ?: 0) ?>"></script>
</body>
</html>
