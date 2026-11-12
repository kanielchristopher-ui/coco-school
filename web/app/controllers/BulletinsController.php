<?php
/**
 * ============================================================
 *  BulletinsController — bulletins périodiques (idée Coco School)
 *
 *  Flux :  l'enseignant saisit les points (période / examen)
 *          → le responsable GÉNÈRE le bulletin (brouillon)
 *          → VALIDE (contrôle de complétude) → PUBLIE
 *          → élèves et parents voient uniquement les bulletins publiés.
 *  Un bulletin publié n'est jamais modifié : une correction crée une
 *  nouvelle version, et chaque action est écrite dans le journal d'audit.
 * ============================================================
 */

declare(strict_types=1);

use CocoCore\Security\SchoolRolePolicy;
use CocoCore\Plugins\CCPLG\PackageException;

class BulletinsController extends Controller
{
    private SchoolRolePolicy $policy;
    private Database $db;

    private const STAFF = ['super_admin', 'admin', 'director', 'prefet', 'proviseur'];

    public function __construct()
    {
        parent::__construct();
        $this->policy = new SchoolRolePolicy();
        $this->db = Database::getInstance();
    }

    // ------------------------------------------------------------------ utilitaires

    private function sessionId(): int
    {
        return BulletinEngine::currentSessionId();
    }

    /** Niveaux (primaire / secondaire) que l'utilisateur peut gérer (valider au minimum). */
    private function managedLevels(): array
    {
        return array_values(array_filter(BulletinEngine::LEVELS, fn($l) => $this->policy->canValidate(Auth::role(), $l)));
    }

    private function cyclesFor(array $levels): array
    {
        $c = [];
        if (in_array('primaire', $levels, true)) {
            array_push($c, 'maternel', 'primaire');
        }
        if (in_array('secondaire', $levels, true)) {
            $c[] = 'secondaire';
        }
        return $c;
    }

    private function audit(int $bulletinId, string $action, array $details = []): void
    {
        $this->db->insert('bulletin_audit_log', [
            'bulletin_id' => $bulletinId, 'actor_id' => Auth::id(), 'action' => $action,
            'details_json' => $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }

    private function teacherId(): int
    {
        return (int) $this->db->fetchColumn('SELECT id FROM teachers WHERE user_id = ?', [Auth::id()]);
    }

    private function latestBulletin(int $studentId, int $sessionId, string $periodId): ?array
    {
        return $this->db->fetch(
            'SELECT * FROM periodic_bulletins WHERE student_id = ? AND academic_year_id = ? AND period_id = ? ORDER BY version DESC LIMIT 1',
            [$studentId, $sessionId, $periodId]
        );
    }

    private function templateFor(string $level): string
    {
        $id = settings('bulletin_template_' . $level, BulletinTemplates::DEFAULT_ID);
        return isset(BulletinTemplates::catalog()[$id]) ? $id : BulletinTemplates::DEFAULT_ID;
    }

    private function levelOfBulletin(array $b): string
    {
        $cycle = (string) $this->db->fetchColumn('SELECT cycle FROM classes WHERE id = ?', [$b['class_id']]);
        return BulletinEngine::level($cycle ?: 'primaire');
    }

    /** Un bulletin est verrouillé (notes non modifiables) si la dernière version est publiée. */
    private function isLocked(int $studentId, int $sessionId, string $termId): bool
    {
        foreach ([$termId, 'YEAR'] as $period) {
            $b = $this->latestBulletin($studentId, $sessionId, $period);
            if ($b && $b['status'] === 'published') {
                return true;
            }
        }
        return false;
    }

    private function canSee(array $b): bool
    {
        $role = Auth::role();
        $student = $this->db->fetch('SELECT user_id FROM students WHERE id = ?', [$b['student_id']]);
        $linked = false;
        if ($role === 'parent') {
            $linked = (bool) $this->db->fetch(
                'SELECT 1 FROM student_parents sp INNER JOIN parents p ON p.id = sp.parent_id WHERE sp.student_id = ? AND p.user_id = ?',
                [$b['student_id'], Auth::id()]
            );
        }
        if (in_array($role, ['super_admin', 'admin'], true)) {
            return true;
        }
        if (in_array($role, ['director', 'prefet', 'proviseur'], true)) {
            return $this->policy->canValidate($role, $this->levelOfBulletin($b));
        }
        return $this->policy->canViewBulletin($role, (string) $b['status'], Auth::id(), (int) ($student['user_id'] ?? 0), $linked);
    }

    // ------------------------------------------------------------------ pilotage (direction)

    public function index(): void
    {
        Auth::requireRole(self::STAFF);
        $levels = $this->managedLevels();
        $cycles = $this->cyclesFor($levels);
        $sessionId = $this->sessionId();

        $classes = $cycles ? $this->db->fetchAll(
            'SELECT id, name, cycle FROM classes WHERE cycle IN (' . implode(',', array_fill(0, count($cycles), '?')) . ') ORDER BY FIELD(cycle, \'maternel\', \'primaire\', \'secondaire\'), CAST(numeric_name AS UNSIGNED), name',
            $cycles
        ) : [];

        $classId = (int) ($_GET['class_id'] ?? ($classes[0]['id'] ?? 0));
        $class = null;
        foreach ($classes as $c) {
            if ((int) $c['id'] === $classId) {
                $class = $c;
            }
        }
        $level = $class ? BulletinEngine::level($class['cycle']) : ($levels[0] ?? 'primaire');
        $scheme = BulletinEngine::scheme($level, $sessionId);
        $periods = $scheme ? BulletinEngine::periods($scheme['config']) : [];
        $periodId = (string) ($_GET['period'] ?? array_key_first($periods) ?? '');
        if ($periods && !isset($periods[$periodId])) {
            $periodId = (string) array_key_first($periods);
        }

        $rows = [];
        if ($class && $scheme && $periodId !== '') {
            $subjects = $this->db->fetchAll('SELECT id, name FROM subjects WHERE class_id = ? ORDER BY id', [$classId]);
            $marks = BulletinEngine::classMarks($classId, $sessionId);
            foreach ($this->db->fetchAll('SELECT * FROM students WHERE class_id = ? AND status = \'active\' ORDER BY first_name, middle_name', [$classId]) as $st) {
                $tbl = BulletinEngine::table($subjects, $marks[(int) $st['id']] ?? [], $scheme['config'], $periodId);
                $rows[] = [
                    'student' => $st, 'bulletin' => $this->latestBulletin((int) $st['id'], $sessionId, $periodId),
                    'complete' => $tbl['complete'], 'percent' => $tbl['max'] > 0 ? round($tbl['points'] / $tbl['max'] * 100, 1) : 0,
                    'has_marks' => !empty($marks[(int) $st['id']]),
                ];
            }
        }

        $this->render('bulletins/index', [
            'classes' => $classes, 'class' => $class, 'level' => $level, 'scheme' => $scheme, 'periods' => $periods, 'periodId' => $periodId,
            'rows' => $rows, 'canPublish' => $this->policy->canPublish(Auth::role(), $level), 'canManage' => $this->policy->canEditScheme(Auth::role(), $level),
            'templates' => BulletinTemplates::forLevel($level), 'template' => $this->templateFor($level), 'sessionId' => $sessionId,
        ]);
    }

    /** Génère (ou régénère) un bulletin ; retourne true si créé/mis à jour. */
    private function generateOne(int $studentId, int $sessionId, string $periodId, string $templateId): bool
    {
        $snap = BulletinEngine::snapshot($studentId, $sessionId, $periodId);
        $classId = (int) $snap['student']['class_id'];
        $latest = $this->latestBulletin($studentId, $sessionId, $periodId);
        $json = json_encode($snap, JSON_UNESCAPED_UNICODE);

        if ($latest && $latest['status'] === 'draft') {
            $this->db->update('periodic_bulletins', ['snapshot_json' => $json, 'scheme_version_id' => $snap['scheme_version'], 'template_id' => $templateId, 'class_id' => $classId], 'id = ?', [$latest['id']]);
            $this->audit((int) $latest['id'], 'regenerated', ['percent' => $snap['totals']['percent']]);
            return true;
        }
        $version = $latest ? (int) $latest['version'] + 1 : 1;
        $id = $this->db->insert('periodic_bulletins', [
            'school_id' => 1, 'student_id' => $studentId, 'class_id' => $classId, 'academic_year_id' => $sessionId,
            'scheme_version_id' => $snap['scheme_version'], 'template_id' => $templateId, 'period_id' => $periodId,
            'version' => $version, 'status' => 'draft', 'snapshot_json' => $json, 'created_by' => Auth::id(),
        ]);
        $this->audit($id, $version > 1 ? 'new_version' : 'generated', ['version' => $version, 'percent' => $snap['totals']['percent']]);
        return true;
    }

    public function generate(): void
    {
        CSRF::check();
        Auth::requireRole(self::STAFF);
        $sessionId = $this->sessionId();
        $classId = (int) ($_POST['class_id'] ?? 0);
        $periodId = (string) ($_POST['period'] ?? '');
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $class = $this->db->fetch('SELECT * FROM classes WHERE id = ?', [$classId]);
        $back = 'bulletins/index?class_id=' . $classId . '&period=' . urlencode($periodId);
        if (!$class || !$this->policy->canValidate(Auth::role(), BulletinEngine::level($class['cycle']))) {
            Flash::set('error', 'You do not have permission to access this page.');
            $this->redirect($back);
        }
        $template = (string) ($_POST['template'] ?? BulletinTemplates::DEFAULT_ID);
        if (!isset(BulletinTemplates::catalog()[$template])) {
            $template = BulletinTemplates::DEFAULT_ID;
        }
        try {
            $ids = $studentId > 0 ? [$studentId] : $this->db->fetchCol('SELECT id FROM students WHERE class_id = ? AND status = \'active\'', [$classId]);
            $n = 0;
            foreach ($ids as $sid) {
                // On ne touche pas aux élèves dont la dernière version est déjà validée/publiée, sauf demande explicite.
                $latest = $this->latestBulletin((int) $sid, $sessionId, $periodId);
                if ($studentId === 0 && $latest && $latest['status'] !== 'draft') {
                    continue;
                }
                $n += $this->generateOne((int) $sid, $sessionId, $periodId, $template) ? 1 : 0;
            }
            Flash::set('success', t(':n bulletin(s) generated.', ['n' => $n]));
        } catch (Throwable $e) {
            Flash::set('error', t('Could not generate: :x', ['x' => $e->getMessage()]));
        }
        $this->redirect($back);
    }

    private function changeStatus(string $to): void
    {
        CSRF::check();
        Auth::requireRole(self::STAFF);
        $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? array_map('intval', $_POST['ids']) : [(int) ($_POST['id'] ?? 0)];
        $back = (string) ($_POST['back'] ?? 'bulletins/index');
        $done = 0;
        $skipped = 0;
        foreach ($ids as $id) {
            $b = $this->db->fetch('SELECT * FROM periodic_bulletins WHERE id = ?', [$id]);
            if (!$b) {
                continue;
            }
            $level = $this->levelOfBulletin($b);
            $allowed = $to === 'published' ? $this->policy->canPublish(Auth::role(), $level) : $this->policy->canValidate(Auth::role(), $level);
            $from = $to === 'published' ? 'validated' : 'draft';
            if (!$allowed || $b['status'] !== $from) {
                $skipped++;
                continue;
            }
            $snap = json_decode((string) $b['snapshot_json'], true) ?: [];
            if ($to === 'validated' && empty($snap['totals']['complete'])) {
                $skipped++;       // contrôle de complétude : toutes les notes doivent être saisies
                continue;
            }
            if ($to === 'validated') {
                $this->db->update('periodic_bulletins', ['status' => 'validated', 'validated_by' => Auth::id(), 'validated_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
                $this->audit($id, 'validated');
            } else {
                $this->db->update('periodic_bulletins', ['status' => 'superseded'], 'student_id = ? AND academic_year_id = ? AND period_id = ? AND status = \'published\' AND id <> ?',
                    [$b['student_id'], $b['academic_year_id'], $b['period_id'], $id]);
                $this->db->update('periodic_bulletins', ['status' => 'published', 'published_by' => Auth::id(), 'published_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
                $this->audit($id, 'published', ['version' => $b['version']]);
                $this->notifyFamily((int) $b['student_id'], (string) ($snap['period']['label'] ?? ''));
            }
            $done++;
        }
        if ($done > 0) {
            Flash::set('success', t(':n bulletin(s) updated.', ['n' => $done]));
        }
        if ($skipped > 0) {
            Flash::set('error', t(':n bulletin(s) skipped (not allowed, wrong status, or marks incomplete).', ['n' => $skipped]));
        }
        $this->redirect($back);
    }

    public function validateBulletin(): void
    {
        $this->changeStatus('validated');
    }

    public function publish(): void
    {
        $this->changeStatus('published');
    }

    private function notifyFamily(int $studentId, string $period): void
    {
        $notif = new Notification();
        $users = $this->db->fetchCol(
            'SELECT user_id FROM students WHERE id = ? AND user_id IS NOT NULL
             UNION SELECT p.user_id FROM student_parents sp INNER JOIN parents p ON p.id = sp.parent_id WHERE sp.student_id = ? AND p.user_id IS NOT NULL',
            [$studentId, $studentId]
        );
        foreach ($users as $uid) {
            $notif->create(['user_id' => (int) $uid, 'title' => 'Report card published', 'message' => $period, 'type' => 'success']);
        }
    }

    // ------------------------------------------------------------------ consultation

    public function show(int $id = 0): void
    {
        $b = $this->db->fetch('SELECT * FROM periodic_bulletins WHERE id = ?', [$id]);
        if (!$b || !$this->canSee($b)) {
            http_response_code(404);
            $this->render('errors/404', []);
            return;
        }
        $level = $this->levelOfBulletin($b);
        $this->render('bulletins/show', [
            'bulletin' => $b, 'rendered' => $this->renderBulletin($b),
            'audit' => in_array(Auth::role(), self::STAFF, true) ? $this->db->fetchAll(
                'SELECT a.*, u.first_name, u.last_name FROM bulletin_audit_log a LEFT JOIN users u ON u.id = a.actor_id WHERE a.bulletin_id = ? ORDER BY a.id DESC', [$id]) : [],
            'canValidate' => $this->policy->canValidate(Auth::role(), $level), 'canPublish' => $this->policy->canPublish(Auth::role(), $level),
            'isTeacher' => Auth::role() === 'teacher',
        ]);
    }

    public function printView(int $id = 0): void
    {
        $b = $this->db->fetch('SELECT * FROM periodic_bulletins WHERE id = ?', [$id]);
        if (!$b || !$this->canSee($b)) {
            http_response_code(404);
            exit('404');
        }
        $this->renderRaw('bulletins/print', ['bulletin' => $b, 'rendered' => $this->renderBulletin($b)]);
    }

    private function renderBulletin(array $b): array
    {
        $snap = json_decode((string) $b['snapshot_json'], true) ?: [];
        try {
            return BulletinTemplates::render(BulletinEngine::present($snap, $b), (string) $b['template_id']);
        } catch (Throwable $e) {
            return ['html' => '<div class="alert alert-danger">' . e($e->getMessage()) . '</div>', 'css' => ''];
        }
    }

    /** Élève : ses bulletins publiés ; parent : ceux de ses enfants liés. */
    public function my(): void
    {
        Auth::requireRole(['student', 'parent']);
        if (Auth::role() === 'student') {
            $ids = $this->db->fetchCol('SELECT id FROM students WHERE user_id = ?', [Auth::id()]);
        } else {
            $ids = $this->db->fetchCol('SELECT sp.student_id FROM student_parents sp INNER JOIN parents p ON p.id = sp.parent_id WHERE p.user_id = ?', [Auth::id()]);
        }
        $list = [];
        if ($ids) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $list = $this->db->fetchAll(
                "SELECT b.*, s.first_name, s.middle_name, s.last_name, c.name AS class_name, a.name AS session_name
                 FROM periodic_bulletins b
                 INNER JOIN students s ON s.id = b.student_id
                 LEFT JOIN classes c ON c.id = b.class_id
                 LEFT JOIN academic_sessions a ON a.id = b.academic_year_id
                 WHERE b.student_id IN ($in) AND b.status = 'published'
                 ORDER BY b.academic_year_id DESC, b.period_id, b.published_at DESC",
                array_map('intval', $ids)
            );
        }
        foreach ($list as &$row) {
            $snap = json_decode((string) $row['snapshot_json'], true) ?: [];
            $row['period_label'] = $snap['period']['label'] ?? $row['period_id'];
            $row['percent'] = $snap['totals']['percent'] ?? null;
            $row['rank'] = $snap['totals']['rank'] ?? null;
            $row['class_size'] = $snap['totals']['class_size'] ?? null;
        }
        unset($row);
        $this->render('bulletins/my', ['list' => $list]);
    }

    // ------------------------------------------------------------------ saisie des points

    private function assignedSubjects(): array
    {
        $role = Auth::role();
        if ($role === 'teacher') {
            return $this->db->fetchAll(
                'SELECT sb.id, sb.name, sb.class_id, c.name AS class_name, c.cycle FROM subjects sb INNER JOIN classes c ON c.id = sb.class_id
                 WHERE sb.teacher_id = ? ORDER BY c.cycle, c.name, sb.name',
                [$this->teacherId()]
            );
        }
        $cycles = $this->cyclesFor($this->managedLevels());
        if (!$cycles) {
            return [];
        }
        return $this->db->fetchAll(
            'SELECT sb.id, sb.name, sb.class_id, c.name AS class_name, c.cycle FROM subjects sb INNER JOIN classes c ON c.id = sb.class_id
             WHERE c.cycle IN (' . implode(',', array_fill(0, count($cycles), '?')) . ') ORDER BY c.cycle, c.name, sb.name',
            $cycles
        );
    }

    public function marks(): void
    {
        Auth::requireRole(array_merge(['teacher'], self::STAFF));
        $sessionId = $this->sessionId();
        $subjects = $this->assignedSubjects();
        $subjectId = (int) ($_GET['subject_id'] ?? 0);
        $subject = null;
        foreach ($subjects as $s) {
            if ((int) $s['id'] === $subjectId) {
                $subject = $s;
            }
        }
        $scheme = $subject ? BulletinEngine::scheme(BulletinEngine::level($subject['cycle']), $sessionId) : null;
        $steps = $scheme ? BulletinEngine::inputSteps($scheme['config']) : [];
        $stepId = (string) ($_GET['step_id'] ?? '');
        if ($steps && !isset($steps[$stepId])) {
            $stepId = (string) array_key_first($steps);
        }

        $students = [];
        if ($subject && $scheme && $stepId !== '') {
            $existing = [];
            foreach ($this->db->fetchAll('SELECT student_id, points_obtained FROM student_marks WHERE subject_id = ? AND academic_year_id = ? AND step_id = ?', [$subjectId, $sessionId, $stepId]) as $r) {
                $existing[(int) $r['student_id']] = $r['points_obtained'];
            }
            foreach ($this->db->fetchAll('SELECT * FROM students WHERE class_id = ? AND status = \'active\' ORDER BY first_name, middle_name', [$subject['class_id']]) as $st) {
                $students[] = ['student' => $st, 'points' => $existing[(int) $st['id']] ?? null, 'locked' => $this->isLocked((int) $st['id'], $sessionId, $steps[$stepId]['term'])];
            }
        }
        $this->render('bulletins/marks', [
            'subjects' => $subjects, 'subject' => $subject, 'scheme' => $scheme, 'steps' => $steps, 'stepId' => $stepId, 'students' => $students,
        ]);
    }

    public function saveMarks(): void
    {
        CSRF::check();
        Auth::requireRole(array_merge(['teacher'], self::STAFF));
        $sessionId = $this->sessionId();
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $stepId = (string) ($_POST['step_id'] ?? '');
        $back = 'bulletins/marks?subject_id=' . $subjectId . '&step_id=' . urlencode($stepId);

        $subject = null;
        foreach ($this->assignedSubjects() as $s) {
            if ((int) $s['id'] === $subjectId) {
                $subject = $s;
            }
        }
        $isAssigned = $subject !== null;
        $level = $subject ? BulletinEngine::level($subject['cycle']) : 'primaire';
        $allowed = Auth::role() === 'teacher' ? $this->policy->canEnterMarks(Auth::role(), $isAssigned) : ($isAssigned && $this->policy->canValidate(Auth::role(), $level));
        $scheme = $subject ? BulletinEngine::scheme($level, $sessionId) : null;
        $steps = $scheme ? BulletinEngine::inputSteps($scheme['config']) : [];
        if (!$allowed || !$scheme || !isset($steps[$stepId])) {
            Flash::set('error', 'You can only enter marks for subjects assigned to you.');
            $this->redirect($back);
        }

        $max = $steps[$stepId]['max'];
        $valid = array_column($this->db->fetchAll('SELECT id FROM students WHERE class_id = ? AND status = \'active\'', [$subject['class_id']]), 'id');
        $saved = 0;
        $locked = 0;
        $invalid = 0;
        foreach ((array) ($_POST['points'] ?? []) as $sid => $raw) {
            $sid = (int) $sid;
            if (!in_array($sid, array_map('intval', $valid), true)) {
                continue;
            }
            $raw = trim(str_replace(',', '.', (string) $raw));
            if ($this->isLocked($sid, $sessionId, $steps[$stepId]['term'])) {
                $locked++;
                continue;
            }
            if ($raw === '') {
                $this->db->delete('student_marks', 'student_id = ? AND subject_id = ? AND academic_year_id = ? AND step_id = ?', [$sid, $subjectId, $sessionId, $stepId]);
                continue;
            }
            if (!is_numeric($raw) || (float) $raw < 0 || (float) $raw > $max) {
                $invalid++;
                continue;
            }
            $this->db->run(
                'INSERT INTO student_marks (school_id, student_id, class_id, subject_id, academic_year_id, scheme_version_id, step_id, max_points, points_obtained, entered_by)
                 VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE points_obtained = VALUES(points_obtained), max_points = VALUES(max_points), scheme_version_id = VALUES(scheme_version_id), entered_by = VALUES(entered_by)',
                [$sid, $subject['class_id'], $subjectId, $sessionId, $scheme['version'], $stepId, $max, round((float) $raw, 2), Auth::id()]
            );
            $saved++;
        }
        ActivityLog::log('update', 'bulletins', "Marks saved: subject #$subjectId step $stepId ($saved)");
        Flash::set('success', t(':n mark(s) saved.', ['n' => $saved]));
        if ($locked > 0) {
            Flash::set('error', t(':n student(s) locked: the report card is published. Request a regeneration.', ['n' => $locked]));
        }
        if ($invalid > 0) {
            Flash::set('error', t(':n invalid value(s) ignored (must be between 0 and :max).', ['n' => $invalid, 'max' => $max]));
        }
        $this->redirect($back);
    }

    /** L'enseignant demande la correction d'un bulletin publié (jamais modifié silencieusement). */
    public function requestRegeneration(): void
    {
        CSRF::check();
        Auth::requireRole(['teacher']);
        $id = (int) ($_POST['id'] ?? 0);
        $b = $this->db->fetch('SELECT * FROM periodic_bulletins WHERE id = ?', [$id]);
        if (!$b || $b['status'] !== 'published') {
            Flash::set('error', 'Only published report cards can be corrected.');
            $this->redirect('bulletins/marks');
        }
        $reason = trim((string) ($_POST['reason'] ?? ''));
        $this->audit($id, 'regeneration_requested', ['reason' => $reason, 'teacher' => Auth::name()]);
        $level = $this->levelOfBulletin($b);
        $role = $level === 'secondaire' ? 'prefet' : 'director';
        $notif = new Notification();
        foreach ($this->db->fetchCol('SELECT u.id FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE r.slug IN (?, \'admin\', \'super_admin\') AND u.status = \'active\'', [$role]) as $uid) {
            $notif->create(['user_id' => (int) $uid, 'title' => 'Report card correction requested', 'message' => Auth::name() . ($reason !== '' ? ': ' . $reason : ''), 'type' => 'warning']);
        }
        Flash::set('success', 'Correction request sent to the school management.');
        $this->redirect('bulletins/show/' . $id);
    }

    // ------------------------------------------------------------------ schémas d'évaluation

    public function scheme(): void
    {
        Auth::requireRole(self::STAFF);
        $sessionId = $this->sessionId();
        $levels = array_values(array_filter(BulletinEngine::LEVELS, fn($l) => $this->policy->canEditScheme(Auth::role(), $l)));
        if (!$levels) {
            http_response_code(403);
            $this->render('errors/403', ['message' => 'You do not have permission to access this page.']);
            return;
        }
        $level = (string) ($_GET['level'] ?? $levels[0]);
        if (!in_array($level, $levels, true)) {
            $level = $levels[0];
        }
        $versions = $this->db->fetchAll('SELECT id, version, created_at FROM school_evaluation_schemes WHERE level_code = ? AND academic_year_id = ? ORDER BY version DESC', [$level, $sessionId]);
        $this->render('bulletins/scheme', [
            'levels' => $levels, 'level' => $level, 'scheme' => BulletinEngine::scheme($level, $sessionId), 'versions' => $versions,
            'templates' => BulletinTemplates::forLevel($level), 'template' => $this->templateFor($level),
            'session' => $this->db->fetch('SELECT * FROM academic_sessions WHERE id = ?', [$sessionId]),
        ]);
    }

    public function installScheme(): void
    {
        CSRF::check();
        Auth::requireRole(self::STAFF);
        $level = (string) ($_POST['level'] ?? '');
        $sessionId = $this->sessionId();
        if (!in_array($level, BulletinEngine::LEVELS, true) || !$this->policy->canEditScheme(Auth::role(), $level)) {
            Flash::set('error', 'You do not have permission to access this page.');
            $this->redirect('bulletins/scheme');
        }
        BulletinEngine::installDefault($level, $sessionId, Auth::id());
        ActivityLog::log('create', 'bulletins', "Default evaluation scheme installed ($level)");
        Flash::set('success', 'Evaluation scheme installed.');
        $this->redirect('bulletins/scheme?level=' . $level);
    }

    public function saveScheme(): void
    {
        CSRF::check();
        Auth::requireRole(self::STAFF);
        $level = (string) ($_POST['level'] ?? '');
        $sessionId = $this->sessionId();
        if (!in_array($level, BulletinEngine::LEVELS, true) || !$this->policy->canEditScheme(Auth::role(), $level)) {
            Flash::set('error', 'You do not have permission to access this page.');
            $this->redirect('bulletins/scheme');
        }
        $current = BulletinEngine::scheme($level, $sessionId);
        if (!$current) {
            Flash::set('error', 'Install the evaluation scheme first.');
            $this->redirect('bulletins/scheme?level=' . $level);
        }
        $config = BulletinEngine::applyFormToConfig($current['config'], $_POST);
        BulletinEngine::saveScheme($level, $sessionId, $config, Auth::id());

        $tpl = (string) ($_POST['template'] ?? '');
        if ($tpl !== '' && isset(BulletinTemplates::forLevel($level)[$tpl])) {
            (new Setting())->set('bulletin_template_' . $level, $tpl);
        }
        ActivityLog::log('update', 'bulletins', "Evaluation scheme updated ($level)");
        Flash::set('success', 'Scheme saved as a new version. Existing bulletins keep their version.');
        $this->redirect('bulletins/scheme?level=' . $level);
    }

    // ------------------------------------------------------------------ extensions .ccplg

    private function requireExtensionManager(): void
    {
        Auth::requireRole(self::STAFF);
        foreach (BulletinEngine::LEVELS as $l) {
            if ($this->policy->canManageExtensions(Auth::role(), $l)) {
                return;
            }
        }
        http_response_code(403);
        $this->render('errors/403', ['message' => 'You do not have permission to access this page.']);
        exit;
    }

    public function extensions(): void
    {
        $this->requireExtensionManager();
        $this->render('bulletins/extensions', [
            'installed' => BulletinTemplates::installed(), 'catalog' => BulletinTemplates::catalog(),
            'sodium' => function_exists('sodium_crypto_sign_verify_detached'), 'zip' => class_exists('ZipArchive'),
        ]);
    }

    public function installExtension(): void
    {
        CSRF::check();
        $this->requireExtensionManager();
        $file = $_FILES['package'] ?? null;
        if (!$file || ($file['error'] ?? 1) !== UPLOAD_ERR_OK || !preg_match('/\.ccplg$/i', (string) $file['name'])) {
            Flash::set('error', 'Please choose a valid .ccplg package.');
            $this->redirect('bulletins/extensions');
        }
        if ((int) $file['size'] > (int) BulletinTemplates::config()['max_upload_bytes']) {
            Flash::set('error', 'Package too large.');
            $this->redirect('bulletins/extensions');
        }
        try {
            $res = BulletinTemplates::install((string) $file['tmp_name'], Auth::id());
            ActivityLog::log('create', 'bulletins', 'Extension installed: ' . $res['plugin_id'] . ' ' . $res['version']);
            Flash::set('success', t('Extension :x installed.', ['x' => $res['plugin_id']]));
        } catch (PackageException $e) {
            Flash::set('error', t('Installation refused: :x', ['x' => $e->getMessage()]));
        } catch (Throwable $e) {
            Flash::set('error', t('Installation failed: :x', ['x' => $e->getMessage()]));
        }
        $this->redirect('bulletins/extensions');
    }

    public function extensionStatus(int $id = 0, string $status = 'disabled'): void
    {
        CSRF::check();
        $this->requireExtensionManager();
        BulletinTemplates::setStatus($id, $status);
        Flash::set('success', 'Extension updated.');
        $this->redirect('bulletins/extensions');
    }

    public function removeExtension(int $id = 0): void
    {
        CSRF::check();
        $this->requireExtensionManager();
        BulletinTemplates::remove($id);
        Flash::set('success', 'Extension removed.');
        $this->redirect('bulletins/extensions');
    }
}
