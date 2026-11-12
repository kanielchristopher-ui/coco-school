108 formulaires.

# 03 — Formulaires

| Vue | Méthode | Action | Champs |
|---|---|---|---|
| attendance/index.php | GET | `attendance/index` | class_id, date, section_id |
| attendance/index.php | POST | `attendance/store` | class_id, date, section_id, subject_id |
| attendance/report.php | GET | `attendance/report` | date, month, type, year |
| attendance/teacher.php | GET | `attendance/teacher` | date |
| attendance/teacher.php | POST | `attendance/store-teacher` | date |
| auth/forgot-password.php | POST | `auth/send-reset-link` | email |
| auth/login.php | POST | `auth/authenticate` | password, remember, username |
| auth/reset-password.php | POST | `auth/process-reset` | password, password_confirmation, token |
| bulletins/extensions.php | POST | `bulletins/install-extension` | package |
| bulletins/extensions.php | POST | `bulletins/extension-status/` |  |
| bulletins/extensions.php | POST | `bulletins/remove-extension/` |  |
| bulletins/index.php | GET | `bulletins/index` | class_id, period |
| bulletins/index.php | POST | `bulletins/generate` | class_id, period, template |
| bulletins/index.php | POST | `bulletins/validate-bulletin` | back, ids[] |
| bulletins/index.php | POST | `bulletins/publish` | back, ids[] |
| bulletins/index.php | POST | `bulletins/generate` | class_id, period, student_id, template |
| bulletins/marks.php | GET | `bulletins/marks` | step_id, subject_id |
| bulletins/marks.php | POST | `bulletins/save-marks` | step_id, subject_id |
| bulletins/scheme.php | POST | `bulletins/install-scheme` | level |
| bulletins/scheme.php | POST | `bulletins/save-scheme` | level, template |
| bulletins/show.php | POST | `bulletins/validate-bulletin` | back, id |
| bulletins/show.php | POST | `bulletins/publish` | back, id |
| bulletins/show.php | POST | `bulletins/request-regeneration` | id, reason |
| classes/_form.php | POST | `(courant)` | class_teacher_id, cycle, name, numeric_name |
| classes/manage.php | POST | `classes/add-section/` | capacity, name, room_no |
| classes/manage.php | POST | `classes/store-timetable/` | day_of_week, end_time, period, section_id, start_time, subject_id, teacher_id |
| discipline/index.php | POST | `discipline/store` | description, incident_date, parent_notified, sanction, severity, student_id, type |
| discipline/index.php | GET | `discipline/index` | class_id, q, status |
| discipline/index.php | POST | `discipline/resolve/` |  |
| events/index.php | POST | `events/store` | description, event_date, event_time, event_type, location, title |
| events/index.php | POST | `events/store-holiday` | description, end_date, start_date, title |
| exams/edit.php | POST | `exams/update/` | class_id, exam_type_id, section_id, session_id, start_date, status, subject_id |
| exams/index.php | GET | `(courant)` | class_id, status |
| exams/index.php | POST | `exams/update-status/` | status |
| exams/index.php | POST | `exams/update-status/` | status |
| exams/index.php | POST | `exams/store` | class_id, exam_type_id, section_id, session_id, start_date, status, subject_id |
| exams/types.php | POST | `exams/store-type` | description, name |
| fees/index.php | GET | `fees/index` | q, status |
| fees/index.php | POST | `fees/generate-invoice` | amount, due_date, fee_type_id, student_id |
| fees/receipts.php | GET | `fees/receipts` | q |
| fees/show.php | POST | `fees/add-payment/` | amount, payment_method, remark, transaction_id |
| fees/types.php | POST | `fees/store-type` | amount, description, frequency, name |
| finance/categories.php | POST | `finance/categories-store` | description, name |
| finance/collections.php | POST | `finance/collections-delete-voided` |  |
| finance/collections.php | GET | `finance/collections` | class_id |
| finance/collections.php | POST | `finance/collections-delete/` |  |
| finance/collections.php | POST | `finance/collections-void/` |  |
| finance/collections.php | POST | `finance/collections-store` | amount_paid, fee_id, fee_type, notes, payment_date, payment_method, student_id |
| finance/collections.php | POST | `(courant)` | amount_paid, fee_type, notes, payment_date, payment_method |
| finance/expenses.php | GET | `finance/expenses` | category_id |
| finance/expenses.php | POST | `finance/expenses-store` | amount, category_id, description, expense_date, notes |
| finance/fees.php | GET | `finance/fees` | class_id, status |
| finance/fees.php | POST | `finance/fees-store` | academic_session, amount, due_date, fee_type, month, status, student_id |
| finance/payroll.php | POST | `finance/payroll-store` | employee_id, employee_type, month, notes, payment_date, salary_amount, status |
| finance/receipts.php | GET | `finance/receipts` | q |
| finance/reports.php | GET | `finance/reports` | from, to, type |
| grades/index.php | POST | `grades/store` | marks_obtained, result_date, student_id, subject_id |
| grades/index.php | POST | `grades/update/` | marks_obtained, result_date, student_id, subject_id |
| inventory/index.php | POST | `inventory/store-item` | category_id, location, name, purchase_date, quantity, supplier_id, unit_price |
| inventory/index.php | POST | `inventory/store-purchase` | invoice_no, item_id, purchase_date, quantity, supplier_id, unit_price |
| inventory/index.php | POST | `inventory/store-issue` | issued_to, item_id, quantity, remark |
| inventory/index.php | POST | `inventory/store-category` | name |
| inventory/index.php | POST | `inventory/store-supplier` | address, contact_person, email, name, phone |
| layouts/app_layout.php | GET | `students/index` | q |
| license/index.php | POST | `license/install` | token |
| messages/broadcast.php | POST | `messages/broadcast-send` | body, class_id, role, subject, target |
| messages/compose.php | POST | `messages/send` | body, receiver_id, subject |
| messages/read.php | POST | `messages/reply/` | body, subject |
| parent/dashboard.php | POST | `parent/read-notifications` |  |
| payroll/index.php | POST | `payroll/store` | allowances, deductions, month, staff_id, teacher_id, year |
| results/entry.php | POST | `results/store/` |  |
| results/promotions.php | POST | `results/promote/` | remarks, to_class_id |
| results/review.php | POST | `results/submit/` |  |
| results/review.php | POST | `results/publish/` |  |
| results/review.php | POST | `results/approve/` |  |
| results/review.php | POST | `results/return-results/` | comment |
| results/review.php | POST | `results/publish/` |  |
| results/review.php | POST | `results/return-results/` | comment |
| results/review.php | POST | `results/approve/` |  |
| results/review.php | POST | `results/reopen/` | comment |
| results/review.php | POST | `results/lock/` |  |
| results/review_queue.php | GET | `(courant)` | status |
| results/teacher_entry.php | GET | `results/teacher-entry` | class_id, exam_id, subject_id |
| results/teacher_entry.php | POST | `results/teacher-store` | class_id, exam_id, subject_id |
| settings/index.php | POST | `settings/save` | academic_session_id, academic_year, currency, currency_position, currency_symbol, favicon, logo, principal_name, school_address, school_email, school_motto, school_name, school_phone, school_short_name … |
| settings/index.php | POST | `settings/save` | attendance_cutoff_time, fee_due_days, language |
| settings/index.php | POST | `settings/save` | enable_transport_module |
| settings/index.php | POST | `settings/restore` | backup_file |
| staff/create.php | POST | `staff/store` | basic_salary, department, designation, email, employee_id, first_name, joining_date, last_name, phone, status |
| staff/edit.php | POST | `staff/update/` | basic_salary, department, designation, email, employee_id, first_name, joining_date, last_name, phone, status |
| staff/index.php | GET | `staff/index` | q, status |
| students/_form.php | POST | `(courant)` | account_email, account_password, account_username, address, birth_place, blood_group, city, class_id, dob, email, first_name, gender, guardian_email, guardian_name … |
| students/index.php | GET | `students/index` | class_id, gender, q, section_id, status |
| subjects/_form.php | POST | `(courant)` | class_id, code, credits, description, name, teacher_id |
| teachers/_form.php | POST | `(courant)` | account_email, account_password, account_username, department, email, first_name, last_name, phone, photo, qualification, salary, status, teaching_level |
| teachers/index.php | GET | `teachers/index` | department, q, qualification, status |
| transport/index.php | POST | `transport/store-vehicle` | capacity, driver_id, model, status, vehicle_no |
| transport/index.php | POST | `transport/store-route` | driver_id, end_point, fare, name, start_point, vehicle_id |
| transport/index.php | POST | `transport/store-driver` | license_no, name, phone, status |
| transport/index.php | POST | `transport/assign` | route_id, status, student_id |
| users/create.php | POST | `users/store` | email, linked_student_id, linked_teacher_id, name, password, phone, role_id, status |
| users/edit.php | POST | `users/update/` | email, linked_student_id, linked_teacher_id, name, password, phone, role_id, status |
| users/index.php | GET | `users/index` | q |
| users/profile.php | POST | `profile/update` | first_name, last_name, phone, photo |
| users/profile.php | POST | `profile/change-password` | confirm_password, current_password, new_password |
| users/role-permissions.php | POST | `users/save-role-permissions/` | permissions[] |
| website/contact.php | POST | `contactSubmit` | email, message, name, phone, subject |
| website/home.php | GET | `contact` | email |