# 06 — Rôles et permissions

## Rôles
| id | slug | nom | description |
|---|---|---|---|
| 1 | super_admin | Super Admin | Full system access |
| 2 | admin | Admin | School administrative access |
| 4 | teacher | Teacher | Teaching staff access |
| 5 | student | Student | Student portal access |
| 3 | prefet | Prefect (School Head) | Head of the school: full pedagogical, disciplinary and financial oversight |
| 6 | parent | Parent | Parent portal access |
| 7 | proviseur | Director of Studies | Pedagogy: classes, subjects, exams, results, promotions |
| 8 | discipline | Discipline Director | Discipline, attendance and incidents |
| 9 | cashier | Cashier | Fees, receipts, expenses and payroll |
| 10 | director | School Director | Director of a nursery/primary school: validates and publishes nursery and primary report cards |

## Permissions (27)
| id | slug | module |
|---|---|---|
| 10 | payroll.manage | payroll |
| 13 | transport.manage | transport |
| 14 | inventory.manage | inventory |
| 15 | events.manage | events |
| 16 | messaging.manage | messaging |
| 17 | reports.view | reports |
| 18 | settings.manage | settings |
| 19 | users.manage | users |
| 20 | staff.manage | staff |
| 22 | classes.view | classes |
| 23 | students.view | students |
| 24 | subjects.view | subjects |
| 25 | exams.view | exams |
| 26 | manage_own_exams | exams |
| 27 | enter_results | results |
| 28 | manage_own_attendance | attendance |
| 29 | send_student_messages | messaging |
| 30 | view_assigned_students | students |
| 31 | view_own_results | results |
| 32 | view_own_attendance | attendance |
| 33 | view_own_subjects | subjects |
| 34 | view_teacher_messages | messaging |
| 35 | create_results | results |
| 36 | edit_results | results |
| 37 | publish_results | results |
| 38 | view_class_results | results |
| 39 | view_own_results_only | results |

## Gardes par rôle dans les contrôleurs

- **AttendanceController** : ['admin', 'principal', 'prefet', 'director', 'proviseur', 'discipline'] ×4
- **BulletinsController** : ['student', 'parent'] ×1; ['teacher'] ×1
- **DashboardController** : ['teacher'] ×1; ['student'] ×1
- **ExamController** : ['student'] ×1; ['admin', 'principal', 'prefet', 'director', 'proviseur'] ×4
- **FinanceController** : ['admin', 'principal', 'prefet', 'director', 'cashier'] ×1; ['student'] ×2
- **LicenseController** : ['super_admin', 'admin'] ×1
- **ResultController** : ['admin', 'prefet', 'director', 'proviseur'] ×10