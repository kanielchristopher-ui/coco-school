222 routes recensées.

# 02 — Contrôleurs, routes, vues et gardes d'accès

Route = `controller/méthode` (méthodes camelCase appelées avec des tirets dans l'URL). « Garde » = vérification de rôle/permission dans la méthode ou le constructeur.

## AttendanceController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `attendance/index` | requirePermission('manage_own_attendance'); requirePermission('attendance.manage') | 2 |
| `attendance/store` | requirePermission('manage_own_attendance'); requirePermission('attendance.manage') | 6 |
| `attendance/my-attendance` | requirePermission('view_own_attendance') | 1 |
| `attendance/teacher` | requireRole(['admin', 'principal', 'prefet', 'director', 'proviseur', 'd) | 3 |
| `attendance/store-teacher` | requireRole(['admin', 'principal', 'prefet', 'director', 'proviseur', 'd) | 3 |
| `attendance/report` | requireRole(['admin', 'principal', 'prefet', 'director', 'proviseur', 'd) | 3 |
| `attendance/export` | requireRole(['admin', 'principal', 'prefet', 'director', 'proviseur', 'd) | 2 |

## AuthController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `auth/login` | — | 0 |
| `auth/authenticate` | — | 1 |
| `auth/forgot-password` | — | 0 |
| `auth/send-reset-link` | — | 0 |
| `auth/reset-password` | — | 0 |
| `auth/process-reset` | — | 3 |
| `auth/logout` | — | 0 |

## BulletinsController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `bulletins/index` | requireRole(self::STAFF) | 3 |
| `bulletins/generate` | requireRole(self::STAFF) | 2 |
| `bulletins/validate-bulletin` | — | 0 |
| `bulletins/publish` | — | 0 |
| `bulletins/show` | — | 2 |
| `bulletins/print-view` | — | 1 |
| `bulletins/my` | requireRole(['student', 'parent']) | 3 |
| `bulletins/marks` | requireRole(array_merge(['teacher'], self::STAFF) | 2 |
| `bulletins/save-marks` | requireRole(array_merge(['teacher'], self::STAFF) | 4 |
| `bulletins/request-regeneration` | requireRole(['teacher']) | 2 |
| `bulletins/scheme` | requireRole(self::STAFF) | 2 |
| `bulletins/install-scheme` | requireRole(self::STAFF) | 0 |
| `bulletins/save-scheme` | requireRole(self::STAFF) | 1 |
| `bulletins/extensions` | — | 0 |
| `bulletins/install-extension` | — | 0 |
| `bulletins/extension-status` | — | 0 |
| `bulletins/remove-extension` | — | 0 |

## ClassController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `class/index` | requirePermission('view_assigned_students'); requirePermission('classes.manage') | 0 |
| `class/create` | requirePermission('classes.manage') | 0 |
| `class/store` | requirePermission('classes.manage') | 0 |
| `class/sections-json` | requirePermission('view_assigned_students'); requirePermission('classes.manage') | 0 |
| `class/manage` | requirePermission('classes.manage') | 4 |
| `class/edit` | requirePermission('classes.manage') | 0 |
| `class/update` | requirePermission('classes.manage') | 3 |
| `class/destroy` | requirePermission('classes.manage') | 0 |
| `class/add-section` | requirePermission('classes.manage') | 0 |
| `class/delete-section` | requirePermission('classes.manage') | 0 |
| `class/store-timetable` | requirePermission('classes.manage') | 0 |
| `class/delete-timetable` | requirePermission('classes.manage') | 0 |

## DashboardController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `dashboard/index` | — | 18 |
| `dashboard/cashier` | — | 8 |
| `dashboard/discipline` | — | 9 |
| `dashboard/teacher` | requireRole(['teacher']) | 7 |
| `dashboard/student` | requireRole(['student']) | 4 |

## DisciplineController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `discipline/index` | requirePermission('discipline.manage') | 3 |
| `discipline/store` | requirePermission('discipline.manage') | 1 |
| `discipline/resolve` | requirePermission('discipline.manage') | 1 |
| `discipline/destroy` | requirePermission('discipline.manage') | 0 |

## EventController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `event/index` | requirePermission('events.manage') | 1 |
| `event/store` | requirePermission('events.manage') | 0 |
| `event/destroy` | requirePermission('events.manage') | 0 |
| `event/store-holiday` | requirePermission('events.manage') | 0 |
| `event/destroy-holiday` | requirePermission('events.manage') | 0 |

## ExamController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `exam/index` | requirePermission('manage_own_exams'); requirePermission('exams.manage') | 0 |
| `exam/student-exams` | requireRole(['student']) | 1 |
| `exam/store` | requireAny(['manage_own_exams', 'exams.manage']) | 1 |
| `exam/edit` | requireAny(['manage_own_exams', 'exams.manage']) | 0 |
| `exam/update` | requireAny(['manage_own_exams', 'exams.manage']) | 6 |
| `exam/update-status` | requireAny(['manage_own_exams', 'exams.manage']) | 4 |
| `exam/destroy` | requireRole(['admin', 'principal', 'prefet', 'director', 'proviseur']) | 0 |
| `exam/types` | requireRole(['admin', 'principal', 'prefet', 'director', 'proviseur']) | 0 |
| `exam/store-type` | requireRole(['admin', 'principal', 'prefet', 'director', 'proviseur']) | 0 |
| `exam/destroy-type` | requireRole(['admin', 'principal', 'prefet', 'director', 'proviseur']) | 0 |
| `exam/sections-json` | requirePermission('manage_own_exams'); requirePermission('exams.manage') | 0 |

## FeeController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `fee/index` | requirePermission('fees.manage') | 1 |
| `fee/generate-invoice` | requirePermission('fees.manage') | 0 |
| `fee/show` | requirePermission('fees.manage') | 2 |
| `fee/add-payment` | requirePermission('fees.manage') | 3 |
| `fee/receipt` | requirePermission('fees.manage') | 2 |
| `fee/receipts` | requirePermission('fees.manage') | 1 |
| `fee/types` | requirePermission('fees.manage') | 0 |
| `fee/store-type` | requirePermission('fees.manage') | 0 |
| `fee/destroy-type` | requirePermission('fees.manage') | 0 |

## FinanceController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `finance/index` | — | 0 |
| `finance/fees` | — | 0 |
| `finance/fees-store` | — | 0 |
| `finance/fees-delete` | — | 0 |
| `finance/collections` | — | 0 |
| `finance/collections-store` | — | 2 |
| `finance/collections-update` | — | 2 |
| `finance/collections-void` | — | 1 |
| `finance/collections-delete` | — | 0 |
| `finance/collections-delete-voided` | — | 1 |
| `finance/receipts` | — | 1 |
| `finance/receipt` | — | 1 |
| `finance/my-fees` | requireRole(['student']) | 4 |
| `finance/my-receipt` | requireRole(['student']) | 1 |
| `finance/payroll` | — | 0 |
| `finance/payroll-store` | — | 1 |
| `finance/payroll-delete` | — | 0 |
| `finance/expenses` | — | 0 |
| `finance/expenses-store` | — | 0 |
| `finance/expenses-delete` | — | 0 |
| `finance/categories` | — | 0 |
| `finance/categories-store` | — | 0 |
| `finance/categories-delete` | — | 1 |
| `finance/reports` | — | 0 |
| `finance/report-print` | — | 0 |

## GradeController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `grade/index` | requirePermission('grades.manage') | 3 |
| `grade/store` | requirePermission('grades.manage') | 4 |
| `grade/update` | requirePermission('grades.manage') | 3 |
| `grade/destroy` | requirePermission('grades.manage') | 0 |

## InventoryController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `inventory/index` | requirePermission('inventory.manage') | 0 |
| `inventory/store-category` | requirePermission('inventory.manage') | 0 |
| `inventory/store-supplier` | requirePermission('inventory.manage') | 0 |
| `inventory/store-item` | requirePermission('inventory.manage') | 0 |
| `inventory/store-purchase` | requirePermission('inventory.manage') | 2 |
| `inventory/store-issue` | requirePermission('inventory.manage') | 2 |
| `inventory/destroy-item` | requirePermission('inventory.manage') | 0 |

## LangController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `lang/change` | — | 0 |

## LicenseController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `license/index` | — | 2 |
| `license/install` | — | 0 |

## MessageController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `message/inbox` | requireAny(['send_student_messages', 'view_teacher_messages', 'messagin) | 0 |
| `message/compose` | requireAny(['send_student_messages', 'messaging.manage']) | 2 |
| `message/send` | requireAny(['send_student_messages', 'messaging.manage']) | 1 |
| `message/broadcast` | requireAny(['send_student_messages', 'messaging.manage']) | 2 |
| `message/broadcast-send` | requireAny(['send_student_messages', 'messaging.manage']) | 0 |
| `message/read` | requireAny(['send_student_messages', 'view_teacher_messages', 'messagin) | 1 |
| `message/reply` | requirePermission('view_teacher_messages') | 5 |
| `message/notifications` | — | 2 |

## ParentController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `parent/dashboard` | — | 3 |
| `parent/read-notifications` | — | 1 |

## PayrollController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `payroll/index` | requirePermission('payroll.manage') | 0 |
| `payroll/store` | requirePermission('payroll.manage') | 1 |
| `payroll/payslip` | requirePermission('payroll.manage') | 1 |
| `payroll/destroy` | requirePermission('payroll.manage') | 0 |

## ProfileController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `profile/index` | — | 1 |
| `profile/update` | — | 3 |
| `profile/change-password` | — | 1 |

## ReportController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `report/index` | requirePermission('reports.view') | 0 |
| `report/view` | requirePermission('reports.view') | 11 |
| `report/export` | requirePermission('reports.view') | 3 |

## ResultController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `result/entry` | requireRole(['admin', 'prefet', 'director', 'proviseur']) | 2 |
| `result/store` | requireRole(['admin', 'prefet', 'director', 'proviseur']) | 1 |
| `result/teacher-entry` | requireAny(['create_results', 'edit_results', 'results.manage']) | 2 |
| `result/teacher-store` | requireAny(['create_results', 'edit_results', 'results.manage']) | 6 |
| `result/submit` | requireAny(['create_results', 'edit_results', 'results.manage']) | 4 |
| `result/approve` | requireRole(['admin', 'prefet', 'director', 'proviseur']) | 2 |
| `result/return-results` | requireRole(['admin', 'prefet', 'director', 'proviseur']) | 2 |
| `result/review` | requireAny(['publish_results', 'edit_results', 'create_results', 'view_) | 1 |
| `result/review-queue` | requireRole(['admin', 'prefet', 'director', 'proviseur']) | 1 |
| `result/publish` | requireAny(['publish_results', 'results.manage']) | 3 |
| `result/reopen` | requireRole(['admin', 'prefet', 'director', 'proviseur']) | 2 |
| `result/lock` | requireRole(['admin', 'prefet', 'director', 'proviseur']) | 1 |
| `result/rank` | requireRole(['admin', 'prefet', 'director', 'proviseur']) | 0 |
| `result/report-card` | — | 0 |
| `result/my-results` | requireAny(['view_own_results_only', 'results.manage']) | 1 |
| `result/view` | — | 0 |
| `result/promote` | requireRole(['admin', 'prefet', 'director', 'proviseur']) | 2 |
| `result/promotions` | requireRole(['admin', 'prefet', 'director', 'proviseur']) | 2 |

## SettingsController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `settings/index` | requirePermission('settings.manage') | 1 |
| `settings/save` | requirePermission('settings.manage') | 1 |
| `settings/backup` | requirePermission('settings.manage') | 2 |
| `settings/restore` | requirePermission('settings.manage') | 1 |

## StaffController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `staff/index` | requirePermission('staff.manage') | 1 |
| `staff/create` | requirePermission('staff.manage') | 0 |
| `staff/store` | requirePermission('staff.manage') | 0 |
| `staff/edit` | requirePermission('staff.manage') | 1 |
| `staff/update` | requirePermission('staff.manage') | 3 |
| `staff/delete` | requirePermission('staff.manage') | 0 |

## StudentController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `student/index` | requirePermission('view_assigned_students'); requirePermission('students.manage') | 0 |
| `student/create` | requirePermission('students.manage') | 0 |
| `student/store` | requirePermission('students.manage') | 0 |
| `student/repair-links` | requirePermission('students.manage') | 0 |
| `student/show` | requirePermission('view_assigned_students'); requirePermission('students.manage') | 2 |
| `student/edit` | requirePermission('students.manage') | 1 |
| `student/update` | requirePermission('students.manage') | 3 |
| `student/destroy` | requirePermission('students.manage') | 5 |
| `student/id-card` | requirePermission('students.manage') | 0 |
| `student/id-cards` | requirePermission('students.manage') | 0 |
| `student/export-csv` | requirePermission('students.manage') | 0 |
| `student/export-excel` | requirePermission('students.manage') | 0 |
| `student/export-pdf` | requirePermission('students.manage') | 0 |

## SubjectController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `subject/index` | requirePermission('view_assigned_students'); requirePermission('subjects.manage') | 0 |
| `subject/create` | requirePermission('subjects.manage') | 0 |
| `subject/store` | requirePermission('subjects.manage') | 0 |
| `subject/edit` | requirePermission('subjects.manage') | 0 |
| `subject/update` | requirePermission('subjects.manage') | 3 |
| `subject/destroy` | requirePermission('subjects.manage') | 0 |

## TeacherController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `teacher/index` | requirePermission('teachers.manage') | 0 |
| `teacher/create` | requirePermission('teachers.manage') | 0 |
| `teacher/store` | requirePermission('teachers.manage') | 0 |
| `teacher/repair-links` | requirePermission('teachers.manage') | 0 |
| `teacher/show` | requirePermission('teachers.manage') | 3 |
| `teacher/edit` | requirePermission('teachers.manage') | 0 |
| `teacher/update` | requirePermission('teachers.manage') | 3 |
| `teacher/destroy` | requirePermission('teachers.manage') | 11 |
| `teacher/export-csv` | requirePermission('teachers.manage') | 0 |
| `teacher/export-excel` | requirePermission('teachers.manage') | 0 |
| `teacher/export-pdf` | requirePermission('teachers.manage') | 0 |

## TransportController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `transport/index` | requirePermission('transport.manage') | 1 |
| `transport/store-vehicle` | requirePermission('transport.manage') | 0 |
| `transport/destroy-vehicle` | requirePermission('transport.manage') | 0 |
| `transport/store-route` | requirePermission('transport.manage') | 0 |
| `transport/destroy-route` | requirePermission('transport.manage') | 0 |
| `transport/store-driver` | requirePermission('transport.manage') | 0 |
| `transport/destroy-driver` | requirePermission('transport.manage') | 0 |
| `transport/assign` | requirePermission('transport.manage') | 1 |
| `transport/unassign` | requirePermission('transport.manage') | 0 |

## UserController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `user/index` | requirePermission('users.manage') | 0 |
| `user/create` | requirePermission('users.manage') | 0 |
| `user/store` | requirePermission('users.manage') | 0 |
| `user/edit` | requirePermission('users.manage') | 3 |
| `user/update` | requirePermission('users.manage') | 9 |
| `user/delete` | requirePermission('users.manage') | 0 |
| `user/roles` | requirePermission('users.manage') | 3 |
| `user/role-permissions` | requirePermission('users.manage') | 3 |
| `user/save-role-permissions` | requirePermission('users.manage') | 1 |

## WebsiteController
| Route | Gardes | Lignes SQL |
|---|---|---|
| `website/index` | — | 0 |
| `website/home` | — | 4 |
| `website/about` | — | 0 |
| `website/academics` | — | 0 |
| `website/admissions` | — | 0 |
| `website/teachers` | — | 1 |
| `website/news` | — | 0 |
| `website/contact` | — | 0 |
| `website/contact-submit` | — | 0 |
| `website/portal` | — | 0 |

## Vues par module

- **attendance** : index.php, my_attendance.php, report.php, teacher.php
- **auth** : forgot-password.php, login.php, reset-password.php
- **bulletins** : extensions.php, index.php, marks.php, my.php, print.php, scheme.php, show.php
- **classes** : _form.php, create.php, edit.php, index.php, manage.php
- **dashboard** : cashier.php, discipline.php, index.php, student.php, teacher.php
- **discipline** : index.php
- **errors** : 403.php, 404.php
- **events** : index.php
- **exams** : edit.php, index.php, student.php, types.php
- **fees** : index.php, receipt.php, receipts.php, show.php, types.php
- **finance** : categories.php, collections.php, expenses.php, fees.php, my_fees.php, payroll.php, receipt.php, receipts.php, report_print.php, reports.php
- **grades** : index.php
- **inventory** : index.php
- **layouts** : app_layout.php, auth_layout.php, footer.php, header.php, icons.php, master.php
- **license** : index.php, locked.php
- **messages** : broadcast.php, compose.php, inbox.php, notifications.php, read.php
- **parent** : dashboard.php
- **partials** : school_art.php
- **payroll** : index.php, payslip.php
- **reports** : index.php, view.php
- **results** : entry.php, my_results.php, promotions.php, rank.php, report_card.php, review.php, review_queue.php, teacher_entry.php, view.php
- **settings** : index.php
- **staff** : create.php, edit.php, index.php
- **students** : _form.php, create.php, edit.php, export_pdf.php, id_card.php, id_cards.php, index.php, show.php
- **subjects** : _form.php, create.php, edit.php, index.php
- **teachers** : _form.php, create.php, edit.php, export_pdf.php, index.php, show.php
- **transport** : index.php
- **users** : create.php, edit.php, index.php, profile.php, role-permissions.php, roles.php
- **website** : about.php, academics.php, admissions.php, contact.php, home.php, news.php, portal.php, teachers.php