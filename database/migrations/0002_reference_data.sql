-- 0002 : rôles, permissions, barème des mentions (données de référence, obligatoires)
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- ROLES
-- ---------------------------------------------------------------------
INSERT INTO `roles` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Super Admin', 'super_admin', 'Full system access'),
(2, 'Admin', 'admin', 'School administrative access'),
(4, 'Teacher', 'teacher', 'Teaching staff access'),
(5, 'Student', 'student', 'Student portal access'),
(3, 'Prefect (School Head)', 'prefet', 'Head of the school: full pedagogical, disciplinary and financial oversight'),
(6, 'Parent', 'parent', 'Parent portal access'),
(7, 'Director of Studies', 'proviseur', 'Pedagogy: classes, subjects, exams, results, promotions'),
(8, 'Discipline Director', 'discipline', 'Discipline, attendance and incidents'),
(9, 'Cashier', 'cashier', 'Fees, receipts, expenses and payroll'),
(10, 'School Director', 'director', 'Director of a nursery/primary school: validates and publishes nursery and primary report cards');

-- ---------------------------------------------------------------------
-- PERMISSIONS
-- ---------------------------------------------------------------------
INSERT INTO `permissions` (`id`, `name`, `slug`, `module`) VALUES
(1,  'View Dashboard', 'dashboard.view', 'dashboard'),
(2,  'Manage Students', 'students.manage', 'students'),
(3,  'Manage Teachers', 'teachers.manage', 'teachers'),
(4,  'Manage Classes', 'classes.manage', 'classes'),
(5,  'Manage Subjects', 'subjects.manage', 'subjects'),
(6,  'Manage Attendance', 'attendance.manage', 'attendance'),
(7,  'Manage Exams', 'exams.manage', 'exams'),
(8,  'Manage Results', 'results.manage', 'results'),
(9,  'Manage Fees', 'fees.manage', 'fees'),
(10, 'Manage Payroll', 'payroll.manage', 'payroll'),
(13, 'Manage Transport', 'transport.manage', 'transport'),
(14, 'Manage Inventory', 'inventory.manage', 'inventory'),
(15, 'Manage Events', 'events.manage', 'events'),
(16, 'Manage Messaging', 'messaging.manage', 'messaging'),
(17, 'View Reports', 'reports.view', 'reports'),
(18, 'Manage Settings', 'settings.manage', 'settings'),
(19, 'Manage Users', 'users.manage', 'users'),
(20, 'Manage Staff', 'staff.manage', 'staff'),
(22, 'View Classes', 'classes.view', 'classes'),
(23, 'View Students', 'students.view', 'students'),
(24, 'View Subjects', 'subjects.view', 'subjects'),
(25, 'View Exams', 'exams.view', 'exams'),
(26, 'Manage Own Exams', 'manage_own_exams', 'exams'),
(27, 'Enter Results', 'enter_results', 'results'),
(28, 'Manage Own Attendance', 'manage_own_attendance', 'attendance'),
(29, 'Send Student Messages', 'send_student_messages', 'messaging'),
(30, 'View Assigned Students', 'view_assigned_students', 'students'),
(31, 'View Own Results', 'view_own_results', 'results'),
(32, 'View Own Attendance', 'view_own_attendance', 'attendance'),
(33, 'View Own Subjects', 'view_own_subjects', 'subjects'),
(34, 'View Teacher Messages', 'view_teacher_messages', 'messaging'),
(35, 'Create Results', 'create_results', 'results'),
(36, 'Edit Results', 'edit_results', 'results'),
(37, 'Publish Results', 'publish_results', 'results'),
(38, 'View Class Results', 'view_class_results', 'results'),
(39, 'View Own Results Only', 'view_own_results_only', 'results');

-- ---------------------------------------------------------------------
-- ROLE PERMISSIONS (Super Admin + Admin get everything)
-- ---------------------------------------------------------------------
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions`;

INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, `id` FROM `permissions`;

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
-- Teacher: teaching-scoped permissions only (own exams, create/edit results,
-- own-class attendance, messaging own students, assigned-student views).
-- Note: teachers do NOT get publish_results - they submit drafts which the
-- admin/super admin review and publish.
(4, 26), (4, 28), (4, 29), (4, 30), (4, 35), (4, 36), (4, 38),
-- Student: read-only portal permissions only (own data is scoped in the
-- controllers; nothing can be edited by this role).
(5, 32), (5, 33), (5, 34), (5, 39),
(6, 1), (6, 8), (6, 9), (6, 16);

-- ---------------------------------------------------------------------
-- BARÈME DES MENTIONS (modifiable dans le menu « Barème des notes »)
-- Seuil de réussite : 50 %
-- ---------------------------------------------------------------------
INSERT INTO `grades` (`grade`, `min_percent`, `max_percent`, `point`, `remarks`) VALUES
('E', 90.0, 100.0, 4.0, 'Excellent'),
('TB', 80.0, 89.99, 3.5, 'Très bien'),
('B', 70.0, 79.99, 3.0, 'Bien'),
('AB', 60.0, 69.99, 2.5, 'Assez bien'),
('S', 50.0, 59.99, 2.0, 'Satisfaisant'),
('Éch', 0.0, 49.99, 0.0, 'Échec');

-- ------------------------------------------------------------
-- 5. permissions for grade scale management
-- ------------------------------------------------------------
INSERT INTO `permissions` (`name`, `slug`, `module`)
SELECT 'Manage Grades', 'grades.manage', 'grades'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `slug` = 'grades.manage');

INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT rp.role_id, p.id
FROM `permissions` p
JOIN (SELECT DISTINCT `role_id` FROM `role_permissions` WHERE `role_id` IN (1, 2, 3)) rp
WHERE p.slug = 'grades.manage'
  AND NOT EXISTS (
    SELECT 1 FROM `role_permissions` r2
    WHERE r2.role_id = rp.role_id AND r2.permission_id = p.id
  );

-- ============================================================

-- ---------------------------------------------------------------------
-- PERMISSIONS DES NOUVEAUX RÔLES
-- ---------------------------------------------------------------------
INSERT INTO `permissions` (`name`, `slug`, `module`)
SELECT 'Manage Discipline', 'discipline.manage', 'discipline'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `slug` = 'discipline.manage');

-- Préfet : tout sauf utilisateurs et paramètres techniques
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, `id` FROM `permissions` WHERE `slug` NOT IN ('users.manage', 'settings.manage');

-- Super Admin et Admin reçoivent aussi la discipline
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p WHERE r.id IN (1, 2) AND p.slug = 'discipline.manage';

-- Directeur d'école (maternel/primaire) : comme le préfet, sans utilisateurs ni paramètres techniques
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 10, `id` FROM `permissions` WHERE `slug` NOT IN ('users.manage', 'settings.manage');

-- Directeur des études (proviseur)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 7, `id` FROM `permissions` WHERE `slug` IN (
 'dashboard.view','students.manage','students.view','teachers.manage','classes.manage','classes.view','subjects.manage','subjects.view',
 'attendance.manage','exams.manage','exams.view','results.manage','publish_results','grades.manage','reports.view','events.manage','messaging.manage');

-- Directeur de discipline
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 8, `id` FROM `permissions` WHERE `slug` IN (
 'dashboard.view','attendance.manage','discipline.manage','events.manage','messaging.manage','reports.view');

-- Caissière
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 9, `id` FROM `permissions` WHERE `slug` IN ('dashboard.view','fees.manage','payroll.manage','reports.view');

SET FOREIGN_KEY_CHECKS = 1;
