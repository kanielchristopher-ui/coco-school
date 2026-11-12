# 04 — Schéma de la base de données

74 tables.

## `roles`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `name` VARCHAR(60) NOT NULL
- `slug` VARCHAR(60) NOT NULL UNIQUE
- `description` VARCHAR(255) DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : —

## `permissions`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `name` VARCHAR(100) NOT NULL
- `slug` VARCHAR(100) NOT NULL UNIQUE
- `module` VARCHAR(60) DEFAULT NULL
- PK : `id` | UNIQUE : — | INDEX : —

## `role_permissions`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `role_id` INT UNSIGNED NOT NULL
- `permission_id` INT UNSIGNED NOT NULL
- PK : `id` | UNIQUE : `role_id`, `permission_id` | INDEX : —

## `users`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `role_id` INT UNSIGNED NOT NULL
- `username` VARCHAR(60) NOT NULL UNIQUE
- `email` VARCHAR(120) NOT NULL UNIQUE
- `password` VARCHAR(255) NOT NULL
- `first_name` VARCHAR(60) DEFAULT NULL
- `last_name` VARCHAR(60) DEFAULT NULL
- `phone` VARCHAR(30) DEFAULT NULL
- `photo` VARCHAR(255) DEFAULT NULL
- `status` ENUM('active','inactive','suspended','pending') NOT NULL DEFAULT 'active'
- `last_login` DATETIME DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : `role_id`

## `password_resets`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `user_id` INT UNSIGNED NOT NULL
- `email` VARCHAR(120) NOT NULL
- `token` VARCHAR(255) NOT NULL
- `expires_at` DATETIME NOT NULL
- `used` TINYINT(1) NOT NULL DEFAULT 0
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : `email`

## `remember_tokens`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `user_id` INT UNSIGNED NOT NULL
- `selector` VARCHAR(64) NOT NULL
- `token_hash` VARCHAR(255) NOT NULL
- `expires_at` DATETIME NOT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : `selector` | INDEX : —

## `academic_sessions`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `name` VARCHAR(60) NOT NULL
- `start_date` DATE DEFAULT NULL
- `end_date` DATE DEFAULT NULL
- `is_current` TINYINT(1) NOT NULL DEFAULT 0
- PK : `id` | UNIQUE : — | INDEX : —

## `classes`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `name` VARCHAR(60) NOT NULL
- `numeric_name` VARCHAR(10) DEFAULT NULL COMMENT 'Rang du niveau dans le cycle (1,2,3...)'
- `cycle` ENUM('maternel','primaire','secondaire') NOT NULL DEFAULT 'primaire'
- `class_teacher_id` INT UNSIGNED DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : `name` | INDEX : —

## `sections`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `class_id` INT UNSIGNED NOT NULL
- `name` VARCHAR(30) NOT NULL
- `room_no` VARCHAR(20) DEFAULT NULL
- `capacity` INT UNSIGNED DEFAULT NULL
- PK : `id` | UNIQUE : `class_id`, `name` | INDEX : —

## `subjects`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `name` VARCHAR(80) NOT NULL
- `code` VARCHAR(20) DEFAULT NULL
- `class_id` INT UNSIGNED DEFAULT NULL
- `teacher_id` INT UNSIGNED DEFAULT NULL
- `credits` INT UNSIGNED DEFAULT NULL
- `description` TEXT
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : `class_id`

## `timetables`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `class_id` INT UNSIGNED NOT NULL
- `section_id` INT UNSIGNED DEFAULT NULL
- `day_of_week` TINYINT UNSIGNED NOT NULL COMMENT '1=Mon ... 7=Sun'
- `period` VARCHAR(30) DEFAULT NULL
- `subject_id` INT UNSIGNED DEFAULT NULL
- `teacher_id` INT UNSIGNED DEFAULT NULL
- `start_time` TIME DEFAULT NULL
- `end_time` TIME DEFAULT NULL
- PK : `id` | UNIQUE : — | INDEX : `class_id`

## `students`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `user_id` INT UNSIGNED DEFAULT NULL
- `official_id` VARCHAR(20) DEFAULT NULL
- `admission_no` VARCHAR(30) NOT NULL UNIQUE
- `roll_no` VARCHAR(20) DEFAULT NULL
- `first_name` VARCHAR(60) NOT NULL
- `middle_name` VARCHAR(60) DEFAULT NULL
- `last_name` VARCHAR(60) DEFAULT NULL
- `gender` ENUM('male','female','other') DEFAULT 'male'
- `dob` DATE DEFAULT NULL
- `birth_place` VARCHAR(100) DEFAULT NULL
- `nationality` VARCHAR(60) DEFAULT 'Congolaise'
- `perm_number` VARCHAR(30) DEFAULT NULL COMMENT 'Numéro PERM (identifiant national de l''élève)'
- `class_id` INT UNSIGNED DEFAULT NULL
- `section_id` INT UNSIGNED DEFAULT NULL
- `guardian_name` VARCHAR(100) DEFAULT NULL
- `guardian_relation` VARCHAR(40) DEFAULT NULL
- `guardian_phone` VARCHAR(30) DEFAULT NULL
- `guardian_email` VARCHAR(120) DEFAULT NULL
- `address` VARCHAR(255) DEFAULT NULL
- `city` VARCHAR(60) DEFAULT NULL
- `state` VARCHAR(60) DEFAULT NULL
- `zip` VARCHAR(15) DEFAULT NULL
- `phone` VARCHAR(30) DEFAULT NULL
- `email` VARCHAR(120) DEFAULT NULL
- `photo` VARCHAR(255) DEFAULT NULL
- `blood_group` VARCHAR(10) DEFAULT NULL
- `medical_info` TEXT
- `status` ENUM('active','inactive','alumni','transferred') NOT NULL DEFAULT 'active'
- `admission_date` DATE DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : `class_id`; `section_id`

## `teachers`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `user_id` INT UNSIGNED DEFAULT NULL
- `official_id` VARCHAR(20) DEFAULT NULL
- `employee_id` VARCHAR(30) NOT NULL UNIQUE
- `first_name` VARCHAR(60) NOT NULL
- `last_name` VARCHAR(60) DEFAULT NULL
- `gender` ENUM('male','female','other') DEFAULT 'male'
- `dob` DATE DEFAULT NULL
- `department` VARCHAR(80) DEFAULT NULL
- `teaching_level` ENUM('maternel','primaire','secondaire') NOT NULL DEFAULT 'secondaire'
- `qualification` VARCHAR(255) DEFAULT NULL
- `specialization` VARCHAR(120) DEFAULT NULL
- `experience_years` INT UNSIGNED DEFAULT 0
- `phone` VARCHAR(30) DEFAULT NULL
- `email` VARCHAR(120) DEFAULT NULL
- `address` VARCHAR(255) DEFAULT NULL
- `salary` DECIMAL(12,2) NOT NULL DEFAULT 0.00
- `joining_date` DATE DEFAULT NULL
- `photo` VARCHAR(255) DEFAULT NULL
- `cv` VARCHAR(255) DEFAULT NULL
- `status` ENUM('active','inactive','left') NOT NULL DEFAULT 'active'
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : —

## `parents`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `user_id` INT UNSIGNED DEFAULT NULL
- `official_id` VARCHAR(20) DEFAULT NULL
- `father_name` VARCHAR(100) DEFAULT NULL
- `mother_name` VARCHAR(100) DEFAULT NULL
- `guardian_name` VARCHAR(100) DEFAULT NULL
- `occupation` VARCHAR(100) DEFAULT NULL
- `phone` VARCHAR(30) DEFAULT NULL
- `email` VARCHAR(120) DEFAULT NULL
- `address` VARCHAR(255) DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : —

## `student_parents`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `student_id` INT UNSIGNED NOT NULL
- `parent_id` INT UNSIGNED NOT NULL
- PK : `id` | UNIQUE : `student_id`, `parent_id` | INDEX : —

## `attendance`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `student_id` INT UNSIGNED NOT NULL
- `class_id` INT UNSIGNED DEFAULT NULL
- `section_id` INT UNSIGNED DEFAULT NULL
- `subject_id` INT UNSIGNED DEFAULT NULL
- `date` DATE NOT NULL
- `status` ENUM('present','absent','late','leave') NOT NULL DEFAULT 'present'
- `remark` VARCHAR(255) DEFAULT NULL
- `taken_by` INT UNSIGNED DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : `student_id`, `date`, `subject_id` | INDEX : —

## `teacher_attendance`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `teacher_id` INT UNSIGNED NOT NULL
- `date` DATE NOT NULL
- `status` ENUM('present','absent','late','leave') NOT NULL DEFAULT 'present'
- `remark` VARCHAR(255) DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : `teacher_id`, `date` | INDEX : —

## `exam_types`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `name` VARCHAR(60) NOT NULL UNIQUE
- `description` VARCHAR(255) DEFAULT NULL
- PK : `id` | UNIQUE : — | INDEX : —

## `grades`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `grade` VARCHAR(10) NOT NULL
- `min_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00
- `max_percent` DECIMAL(5,2) NOT NULL DEFAULT 100.00
- `point` DECIMAL(3,2) NOT NULL DEFAULT 0.00
- `remarks` VARCHAR(255) DEFAULT NULL
- `is_active` TINYINT(1) NOT NULL DEFAULT 1
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : `grade` | INDEX : —

## `exams`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `name` VARCHAR(100) NOT NULL
- `exam_type_id` INT UNSIGNED DEFAULT NULL
- `subject_id` INT UNSIGNED DEFAULT NULL
- `class_id` INT UNSIGNED DEFAULT NULL
- `section_id` INT UNSIGNED DEFAULT NULL
- `session_id` INT UNSIGNED DEFAULT NULL
- `term` VARCHAR(30) DEFAULT NULL
- `start_date` DATE DEFAULT NULL
- `end_date` DATE DEFAULT NULL
- `status` ENUM('draft','published','upcoming','active','completed') NOT NULL DEFAULT 'upcoming'
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : `class_id`; `subject_id`

## `results`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `exam_id` INT UNSIGNED NOT NULL
- `student_id` INT UNSIGNED NOT NULL
- `subject_id` INT UNSIGNED NOT NULL
- `marks_obtained` DECIMAL(6,2) NOT NULL DEFAULT 0
- `max_marks` DECIMAL(6,2) NOT NULL DEFAULT 100
- `grade` VARCHAR(5) DEFAULT NULL
- `remarks` VARCHAR(255) DEFAULT NULL
- `teacher_comments` VARCHAR(255) DEFAULT NULL
- `correction_comment` VARCHAR(500) DEFAULT NULL
- `status` ENUM('draft','submitted','approved','returned','published','locked') NOT NULL DEFAULT 'draft'
- `published_at` DATETIME DEFAULT NULL
- `reopened_at` DATETIME DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : `exam_id`, `student_id`, `subject_id` | INDEX : —

## `promotions`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `student_id` INT UNSIGNED NOT NULL
- `from_class_id` INT UNSIGNED DEFAULT NULL
- `to_class_id` INT UNSIGNED DEFAULT NULL
- `session_id` INT UNSIGNED DEFAULT NULL
- `promoted_date` DATE DEFAULT NULL
- `remarks` VARCHAR(255) DEFAULT NULL
- PK : `id` | UNIQUE : — | INDEX : `student_id`

## `fee_types`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `name` VARCHAR(80) NOT NULL
- `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00
- `frequency` ENUM('one_time','monthly','yearly','term') NOT NULL DEFAULT 'monthly'
- `description` VARCHAR(255) DEFAULT NULL
- `status` TINYINT(1) NOT NULL DEFAULT 1
- PK : `id` | UNIQUE : — | INDEX : —

## `fees`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `invoice_no` VARCHAR(40) NOT NULL UNIQUE
- `student_id` INT UNSIGNED NOT NULL
- `fee_type_id` INT UNSIGNED DEFAULT NULL
- `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00
- `discount_type` ENUM('fixed','percent','none') NOT NULL DEFAULT 'none'
- `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00
- `scholarship` DECIMAL(12,2) NOT NULL DEFAULT 0.00
- `due_date` DATE DEFAULT NULL
- `status` ENUM('pending','partial','paid','overdue','cancelled') NOT NULL DEFAULT 'pending'
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : `student_id`

## `payments`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `receipt_no` VARCHAR(40) NOT NULL UNIQUE
- `fee_id` INT UNSIGNED NOT NULL
- `student_id` INT UNSIGNED NOT NULL
- `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00
- `payment_method` ENUM('cash','card','bank_transfer','cheque','online','mobile_money') NOT NULL DEFAULT 'cash'
- `transaction_id` VARCHAR(100) DEFAULT NULL
- `paid_by` INT UNSIGNED DEFAULT NULL
- `payment_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
- `remark` VARCHAR(255) DEFAULT NULL
- PK : `id` | UNIQUE : — | INDEX : `fee_id`

## `fee_discounts`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `student_id` INT UNSIGNED NOT NULL
- `title` VARCHAR(100) DEFAULT NULL
- `discount_type` ENUM('fixed','percent') NOT NULL DEFAULT 'fixed'
- `value` DECIMAL(12,2) NOT NULL DEFAULT 0.00
- `reason` VARCHAR(255) DEFAULT NULL
- `valid_from` DATE DEFAULT NULL
- `valid_to` DATE DEFAULT NULL
- PK : `id` | UNIQUE : — | INDEX : `student_id`

## `staff`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `user_id` INT UNSIGNED DEFAULT NULL
- `employee_id` VARCHAR(30) NOT NULL UNIQUE
- `first_name` VARCHAR(60) NOT NULL
- `last_name` VARCHAR(60) DEFAULT NULL
- `department` VARCHAR(80) DEFAULT NULL
- `designation` VARCHAR(80) DEFAULT NULL
- `phone` VARCHAR(30) DEFAULT NULL
- `email` VARCHAR(120) DEFAULT NULL
- `joining_date` DATE DEFAULT NULL
- `basic_salary` DECIMAL(12,2) NOT NULL DEFAULT 0.00
- `status` ENUM('active','inactive','left') NOT NULL DEFAULT 'active'
- PK : `id` | UNIQUE : — | INDEX : —

## `salary`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `staff_id` INT UNSIGNED DEFAULT NULL COMMENT 'For non-teaching staff'
- `teacher_id` INT UNSIGNED DEFAULT NULL COMMENT 'For teaching staff'
- `month` TINYINT UNSIGNED NOT NULL
- `year` SMALLINT UNSIGNED NOT NULL
- `basic_salary` DECIMAL(12,2) NOT NULL DEFAULT 0.00
- `allowances` DECIMAL(12,2) NOT NULL DEFAULT 0.00
- `deductions` DECIMAL(12,2) NOT NULL DEFAULT 0.00
- `net_salary` DECIMAL(12,2) NOT NULL DEFAULT 0.00
- `payment_date` DATE DEFAULT NULL
- `status` ENUM('paid','pending','draft') NOT NULL DEFAULT 'pending'
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : `month`, `year`

## `drivers`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `name` VARCHAR(100) NOT NULL
- `license_no` VARCHAR(60) DEFAULT NULL
- `phone` VARCHAR(30) DEFAULT NULL
- `address` VARCHAR(255) DEFAULT NULL
- `status` ENUM('active','inactive') NOT NULL DEFAULT 'active'
- PK : `id` | UNIQUE : — | INDEX : —

## `vehicles`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `vehicle_no` VARCHAR(30) NOT NULL UNIQUE
- `model` VARCHAR(80) DEFAULT NULL
- `capacity` INT UNSIGNED DEFAULT NULL
- `driver_id` INT UNSIGNED DEFAULT NULL
- `status` ENUM('active','inactive','maintenance') NOT NULL DEFAULT 'active'
- PK : `id` | UNIQUE : — | INDEX : —

## `routes`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `name` VARCHAR(100) NOT NULL
- `stops` TEXT
- `fare` DECIMAL(10,2) NOT NULL DEFAULT 0.00
- `status` TINYINT(1) NOT NULL DEFAULT 1
- PK : `id` | UNIQUE : — | INDEX : —

## `transport_assignments`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `student_id` INT UNSIGNED NOT NULL
- `route_id` INT UNSIGNED DEFAULT NULL
- `vehicle_id` INT UNSIGNED DEFAULT NULL
- `pickup_point` VARCHAR(120) DEFAULT NULL
- `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00
- `status` ENUM('active','inactive') NOT NULL DEFAULT 'active'
- PK : `id` | UNIQUE : — | INDEX : `student_id`

## `inventory_categories`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `name` VARCHAR(80) NOT NULL UNIQUE
- PK : `id` | UNIQUE : — | INDEX : —

## `suppliers`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `name` VARCHAR(120) NOT NULL
- `contact_person` VARCHAR(80) DEFAULT NULL
- `phone` VARCHAR(30) DEFAULT NULL
- `email` VARCHAR(120) DEFAULT NULL
- `address` VARCHAR(255) DEFAULT NULL
- PK : `id` | UNIQUE : — | INDEX : —

## `inventory_items`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `name` VARCHAR(120) NOT NULL
- `category_id` INT UNSIGNED DEFAULT NULL
- `supplier_id` INT UNSIGNED DEFAULT NULL
- `quantity` INT UNSIGNED NOT NULL DEFAULT 0
- `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00
- `purchase_date` DATE DEFAULT NULL
- `location` VARCHAR(120) DEFAULT NULL
- `status` ENUM('in_stock','low_stock','out_of_stock') NOT NULL DEFAULT 'in_stock'
- PK : `id` | UNIQUE : — | INDEX : `category_id`

## `purchases`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `item_id` INT UNSIGNED NOT NULL
- `supplier_id` INT UNSIGNED DEFAULT NULL
- `quantity` INT UNSIGNED NOT NULL DEFAULT 1
- `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00
- `total` DECIMAL(12,2) NOT NULL DEFAULT 0.00
- `invoice_no` VARCHAR(40) DEFAULT NULL
- `purchase_date` DATE DEFAULT NULL
- PK : `id` | UNIQUE : — | INDEX : `item_id`

## `stock_issues`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `item_id` INT UNSIGNED NOT NULL
- `issued_to` VARCHAR(120) DEFAULT NULL
- `quantity` INT UNSIGNED NOT NULL DEFAULT 1
- `issue_date` DATE DEFAULT NULL
- `remark` VARCHAR(255) DEFAULT NULL
- PK : `id` | UNIQUE : — | INDEX : `item_id`

## `events`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `title` VARCHAR(150) NOT NULL
- `description` TEXT
- `event_date` DATE NOT NULL
- `event_time` TIME DEFAULT NULL
- `event_type` ENUM('event','meeting','holiday','exam','sport') NOT NULL DEFAULT 'event'
- `location` VARCHAR(120) DEFAULT NULL
- `created_by` INT UNSIGNED DEFAULT NULL
- PK : `id` | UNIQUE : — | INDEX : —

## `holidays`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `title` VARCHAR(150) NOT NULL
- `start_date` DATE NOT NULL
- `end_date` DATE NOT NULL
- `description` VARCHAR(255) DEFAULT NULL
- PK : `id` | UNIQUE : — | INDEX : —

## `messages`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `sender_id` INT UNSIGNED NOT NULL
- `receiver_id` INT UNSIGNED NOT NULL
- `subject` VARCHAR(150) DEFAULT NULL
- `body` TEXT
- `is_read` TINYINT(1) NOT NULL DEFAULT 0
- `read_at` DATETIME DEFAULT NULL
- `parent_id` INT UNSIGNED DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : `sender_id`; `receiver_id`

## `notifications`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `user_id` INT UNSIGNED NOT NULL
- `title` VARCHAR(150) NOT NULL
- `message` TEXT
- `link` VARCHAR(255) DEFAULT NULL
- `type` VARCHAR(40) DEFAULT 'info'
- `is_read` TINYINT(1) NOT NULL DEFAULT 0
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : `user_id`

## `activity_logs`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `user_id` INT UNSIGNED DEFAULT NULL
- `action` VARCHAR(80) NOT NULL
- `module` VARCHAR(60) DEFAULT NULL
- `description` VARCHAR(255) DEFAULT NULL
- `ip_address` VARCHAR(45) DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : `user_id`; `module`

## `settings`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `setting_key` VARCHAR(80) NOT NULL UNIQUE
- `setting_value` TEXT
- PK : `id` | UNIQUE : — | INDEX : —

## `discipline_records`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `student_id` INT UNSIGNED NOT NULL
- `incident_date` DATE NOT NULL
- `type` VARCHAR(60) NOT NULL
- `severity` ENUM('minor','moderate','serious') NOT NULL DEFAULT 'minor'
- `description` TEXT DEFAULT NULL
- `sanction` VARCHAR(100) DEFAULT NULL
- `parent_notified` TINYINT(1) NOT NULL DEFAULT 0
- `status` ENUM('open','resolved') NOT NULL DEFAULT 'open'
- `recorded_by` INT UNSIGNED DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : `student_id`; `status`

## `official_id_counters`
- `prefix` VARCHAR(10) NOT NULL
- `last_number` INT UNSIGNED NOT NULL DEFAULT 0
- PK : `prefix` | UNIQUE : — | INDEX : —

## `grades`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `grade` VARCHAR(10) NOT NULL
- `min_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00
- `max_percent` DECIMAL(5,2) NOT NULL DEFAULT 100.00
- `point` DECIMAL(3,2) NOT NULL DEFAULT 0.00
- `remarks` VARCHAR(255) DEFAULT NULL
- `is_active` TINYINT(1) NOT NULL DEFAULT 1
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : `grade` | INDEX : —

## `student_fees`
- id INT UNSIGNED NOT NULL AUTO_INCREMENT
- student_id INT UNSIGNED NOT NULL
- class_id INT UNSIGNED NULL
- fee_type VARCHAR(40) NOT NULL
- amount DECIMAL(12,2) NOT NULL DEFAULT 0.00
- academic_session VARCHAR(60) NOT NULL
- month VARCHAR(20) NULL
- due_date DATE NULL
- status ENUM('pending','partial','paid') NOT NULL DEFAULT 'pending'
- created_by INT UNSIGNED NULL
- created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- PK : id | UNIQUE : — | INDEX : student_id; class_id

## `fee_collections`
- id INT UNSIGNED NOT NULL AUTO_INCREMENT
- student_id INT UNSIGNED NOT NULL
- class_id INT UNSIGNED NULL
- fee_type VARCHAR(40) NOT NULL
- amount_paid DECIMAL(12,2) NOT NULL DEFAULT 0.00
- payment_date DATE NOT NULL
- payment_method VARCHAR(20) NOT NULL DEFAULT 'cash'
- collected_by INT UNSIGNED NULL
- notes VARCHAR(255) NULL
- receipt_no VARCHAR(30) NOT NULL
- status ENUM('active','voided') NOT NULL DEFAULT 'active'
- created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- PK : id | UNIQUE : receipt_no | INDEX : student_id; class_id

## `payrolls`
- id INT UNSIGNED NOT NULL AUTO_INCREMENT
- employee_type ENUM('teacher','staff') NOT NULL
- employee_id INT UNSIGNED NOT NULL
- position VARCHAR(80) NULL
- salary_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00
- month VARCHAR(7) NOT NULL
- payment_date DATE NOT NULL
- status ENUM('paid','pending') NOT NULL DEFAULT 'pending'
- notes VARCHAR(255) NULL
- created_by INT UNSIGNED NULL
- created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- PK : id | UNIQUE : — | INDEX : employee_type, employee_id

## `expense_categories`
- id INT UNSIGNED NOT NULL AUTO_INCREMENT
- name VARCHAR(80) NOT NULL
- description VARCHAR(255) NULL
- created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- PK : id | UNIQUE : name | INDEX : —

## `expenses`
- id INT UNSIGNED NOT NULL AUTO_INCREMENT
- category_id INT UNSIGNED NOT NULL
- description VARCHAR(255) NOT NULL
- amount DECIMAL(12,2) NOT NULL DEFAULT 0.00
- expense_date DATE NOT NULL
- paid_by INT UNSIGNED NULL
- notes VARCHAR(255) NULL
- created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- PK : id | UNIQUE : — | INDEX : category_id

## `school_extensions`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `school_id` INT UNSIGNED NOT NULL DEFAULT 1
- `plugin_id` VARCHAR(80) NOT NULL
- `name` VARCHAR(150) NOT NULL
- `version` VARCHAR(30) NOT NULL
- `package_sha256` CHAR(64) NOT NULL
- `manifest_json` LONGTEXT DEFAULT NULL
- `installed_by` INT UNSIGNED DEFAULT NULL
- `installed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- `status` ENUM('active','disabled') NOT NULL DEFAULT 'active'
- PK : `id` | UNIQUE : `school_id`, `plugin_id` | INDEX : —

## `school_evaluation_schemes`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `school_id` INT UNSIGNED NOT NULL DEFAULT 1
- `level_code` VARCHAR(30) NOT NULL COMMENT 'primaire (maternel + primaire) ou secondaire'
- `academic_year_id` INT UNSIGNED NOT NULL
- `scheme_id` VARCHAR(80) NOT NULL
- `version` INT UNSIGNED NOT NULL DEFAULT 1
- `config_json` LONGTEXT NOT NULL
- `created_by` INT UNSIGNED DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : `school_id`, `level_code`, `academic_year_id`, `version` | INDEX : `academic_year_id`

## `student_marks`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `school_id` INT UNSIGNED NOT NULL DEFAULT 1
- `student_id` INT UNSIGNED NOT NULL
- `class_id` INT UNSIGNED NOT NULL
- `subject_id` INT UNSIGNED NOT NULL
- `academic_year_id` INT UNSIGNED NOT NULL
- `scheme_version_id` INT UNSIGNED NOT NULL
- `step_id` VARCHAR(40) NOT NULL
- `max_points` DECIMAL(8,2) NOT NULL
- `points_obtained` DECIMAL(8,2) NOT NULL
- `entered_by` INT UNSIGNED DEFAULT NULL
- `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : `student_id`, `subject_id`, `academic_year_id`, `step_id` | INDEX : `class_id`, `academic_year_id`

## `periodic_bulletins`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `school_id` INT UNSIGNED NOT NULL DEFAULT 1
- `student_id` INT UNSIGNED NOT NULL
- `class_id` INT UNSIGNED NOT NULL
- `academic_year_id` INT UNSIGNED NOT NULL
- `scheme_version_id` INT UNSIGNED NOT NULL
- `template_id` VARCHAR(120) NOT NULL DEFAULT 'classique'
- `period_id` VARCHAR(40) NOT NULL COMMENT 'T1, T2, T3, S1, S2 ou YEAR'
- `version` INT UNSIGNED NOT NULL DEFAULT 1
- `status` ENUM('draft','validated','published','superseded') NOT NULL DEFAULT 'draft'
- `snapshot_json` LONGTEXT DEFAULT NULL
- `created_by` INT UNSIGNED DEFAULT NULL
- `validated_by` INT UNSIGNED DEFAULT NULL
- `validated_at` DATETIME DEFAULT NULL
- `published_by` INT UNSIGNED DEFAULT NULL
- `published_at` DATETIME DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : `student_id`, `academic_year_id`, `period_id`, `version` | INDEX : `student_id`, `status`, `academic_year_id`; `class_id`, `academic_year_id`, `period_id`

## `bulletin_audit_log`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `bulletin_id` INT UNSIGNED NOT NULL
- `actor_id` INT UNSIGNED DEFAULT NULL
- `action` VARCHAR(40) NOT NULL
- `details_json` LONGTEXT DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : `bulletin_id`, `created_at`

## `core_audit_log`
- `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT
- `actor_id` INT UNSIGNED DEFAULT NULL
- `action` VARCHAR(60) NOT NULL
- `entity` VARCHAR(60) NOT NULL
- `entity_id` INT UNSIGNED DEFAULT NULL
- `details_json` LONGTEXT DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : `entity`, `entity_id`; `actor_id`, `created_at`

## `establishments`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `code` VARCHAR(30) NOT NULL
- `name` VARCHAR(150) NOT NULL
- `level` ENUM('maternel','primaire','secondaire') NOT NULL
- `regime` ENUM('public','conventionne_catholique','conventionne_protestant','conventionne_kimbanguiste','conventionne_autre','prive') NOT NULL DEFAULT 'prive'
- `head_title` VARCHAR(60) NOT NULL DEFAULT 'Directeur'
- `head_name` VARCHAR(120) DEFAULT NULL
- `province` VARCHAR(80) DEFAULT NULL
- `city` VARCHAR(80) DEFAULT NULL
- `address` VARCHAR(255) DEFAULT NULL
- `phone` VARCHAR(30) DEFAULT NULL
- `email` VARCHAR(120) DEFAULT NULL
- `status` ENUM('active','inactive') NOT NULL DEFAULT 'active'
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : `code` | INDEX : `level`

## `user_establishments`
- `user_id` INT UNSIGNED NOT NULL
- `establishment_id` INT UNSIGNED NOT NULL
- PK : `user_id`, `establishment_id` | UNIQUE : — | INDEX : —

## `school_options`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `code` VARCHAR(20) NOT NULL
- `name` VARCHAR(120) NOT NULL
- `group_name` VARCHAR(120) NOT NULL
- `exetat_code` VARCHAR(20) DEFAULT NULL COMMENT 'code de l\'option à l\'Examen d\'État (à renseigner selon la nomenclature officielle)'
- `status` ENUM('active','inactive') NOT NULL DEFAULT 'active'
- PK : `id` | UNIQUE : `code` | INDEX : —

## `student_enrollments`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `student_id` INT UNSIGNED NOT NULL
- `class_id` INT UNSIGNED NOT NULL
- `section_id` INT UNSIGNED DEFAULT NULL
- `establishment_id` INT UNSIGNED NOT NULL
- `academic_year_id` INT UNSIGNED NOT NULL
- `enrolled_on` DATE NOT NULL
- `status` ENUM('enrolled','promoted','repeating','retake','transferred','withdrawn','graduated') NOT NULL DEFAULT 'enrolled'
- `final_percent` DECIMAL(5,2) DEFAULT NULL
- `decision_note` VARCHAR(255) DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : `student_id`, `academic_year_id` | INDEX : `class_id`, `academic_year_id`

## `development_domains`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `code` VARCHAR(20) NOT NULL
- `name` VARCHAR(100) NOT NULL
- `description` VARCHAR(255) DEFAULT NULL
- `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0
- PK : `id` | UNIQUE : `code` | INDEX : —

## `development_assessments`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `student_id` INT UNSIGNED NOT NULL
- `domain_id` INT UNSIGNED NOT NULL
- `academic_year_id` INT UNSIGNED NOT NULL
- `period_id` VARCHAR(10) NOT NULL
- `level` ENUM('acquired','in_progress','not_acquired') NOT NULL
- `comment` VARCHAR(255) DEFAULT NULL
- `assessed_by` INT UNSIGNED DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : `student_id`, `domain_id`, `academic_year_id`, `period_id` | INDEX : —

## `child_health_records`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `student_id` INT UNSIGNED NOT NULL
- `vaccinations` TEXT DEFAULT NULL
- `allergies` TEXT DEFAULT NULL
- `medical_notes` TEXT DEFAULT NULL
- `doctor_name` VARCHAR(120) DEFAULT NULL
- `doctor_phone` VARCHAR(30) DEFAULT NULL
- `emergency_contact_name` VARCHAR(120) DEFAULT NULL
- `emergency_contact_phone` VARCHAR(30) DEFAULT NULL
- `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : `student_id` | INDEX : —

## `authorized_pickups`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `student_id` INT UNSIGNED NOT NULL
- `full_name` VARCHAR(120) NOT NULL
- `relation` VARCHAR(60) NOT NULL
- `phone` VARCHAR(30) DEFAULT NULL
- `id_document` VARCHAR(60) DEFAULT NULL
- `active` TINYINT(1) NOT NULL DEFAULT 1
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : `student_id`

## `pickup_logs`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `student_id` INT UNSIGNED NOT NULL
- `picked_by_name` VARCHAR(120) NOT NULL
- `authorized` TINYINT(1) NOT NULL
- `recorded_by` INT UNSIGNED DEFAULT NULL
- `picked_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : `student_id`, `picked_at`

## `daily_reports`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `class_id` INT UNSIGNED NOT NULL
- `section_id` INT UNSIGNED DEFAULT NULL
- `report_date` DATE NOT NULL
- `activities` TEXT DEFAULT NULL
- `meals` VARCHAR(255) DEFAULT NULL
- `nap_minutes` SMALLINT UNSIGNED DEFAULT NULL
- `notes_for_parents` TEXT DEFAULT NULL
- `created_by` INT UNSIGNED DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : `class_id`, `section_id`, `report_date` | INDEX : —

## `national_exam_registrations`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `student_id` INT UNSIGNED NOT NULL
- `exam_code` ENUM('TENAFEP','EXETAT') NOT NULL
- `academic_year_id` INT UNSIGNED NOT NULL
- `candidate_number` VARCHAR(40) DEFAULT NULL
- `center` VARCHAR(120) DEFAULT NULL
- `option_id` INT UNSIGNED DEFAULT NULL
- `school_percent` DECIMAL(5,2) DEFAULT NULL
- `exam_percent` DECIMAL(5,2) DEFAULT NULL
- `final_percent` DECIMAL(5,2) DEFAULT NULL
- `result` ENUM('pending','passed','failed','absent') NOT NULL DEFAULT 'pending'
- `mention` VARCHAR(40) DEFAULT NULL
- `registered_on` DATE NOT NULL
- PK : `id` | UNIQUE : `student_id`, `exam_code`, `academic_year_id`; `candidate_number` | INDEX : —

## `retake_exams`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `student_id` INT UNSIGNED NOT NULL
- `subject_id` INT UNSIGNED DEFAULT NULL
- `academic_year_id` INT UNSIGNED NOT NULL
- `original_percent` DECIMAL(5,2) NOT NULL
- `retake_percent` DECIMAL(5,2) DEFAULT NULL
- `result` ENUM('pending','passed','failed') NOT NULL DEFAULT 'pending'
- `exam_date` DATE DEFAULT NULL
- PK : `id` | UNIQUE : — | INDEX : `student_id`, `academic_year_id`

## `conduct_grades`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `student_id` INT UNSIGNED NOT NULL
- `academic_year_id` INT UNSIGNED NOT NULL
- `period_id` VARCHAR(10) NOT NULL
- `score` DECIMAL(5,2) NOT NULL
- `mention` VARCHAR(40) DEFAULT NULL
- `incident_count` SMALLINT UNSIGNED NOT NULL DEFAULT 0
- `comment` VARCHAR(255) DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : `student_id`, `academic_year_id`, `period_id` | INDEX : —

## `fee_plans`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `establishment_id` INT UNSIGNED NOT NULL
- `academic_year_id` INT UNSIGNED NOT NULL
- `class_id` INT UNSIGNED DEFAULT NULL
- `fee_type` VARCHAR(80) NOT NULL
- `amount` DECIMAL(10,2) NOT NULL
- `frequency` ENUM('one_time','monthly','term','semester','yearly') NOT NULL DEFAULT 'term'
- `active` TINYINT(1) NOT NULL DEFAULT 1
- PK : `id` | UNIQUE : — | INDEX : `establishment_id`, `academic_year_id`

## `licenses`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `license_id` VARCHAR(64) NOT NULL
- `token` LONGTEXT NOT NULL
- `holder` VARCHAR(200) NOT NULL
- `valid_from` DATE NOT NULL
- `valid_until` DATE NOT NULL
- `installed_by` INT UNSIGNED DEFAULT NULL
- `installed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- `status` ENUM('active','replaced','revoked') NOT NULL DEFAULT 'active'
- PK : `id` | UNIQUE : `license_id` | INDEX : —

## `license_events`
- `id` INT UNSIGNED NOT NULL AUTO_INCREMENT
- `event` VARCHAR(40) NOT NULL
- `details_json` TEXT DEFAULT NULL
- `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- PK : `id` | UNIQUE : — | INDEX : —

## `system_state`
- `key_name` VARCHAR(60) NOT NULL
- `value_text` TEXT DEFAULT NULL
- `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- PK : `key_name` | UNIQUE : — | INDEX : —

## Relations (clés étrangères)

| Table | Colonne | Référence | Action |
|---|---|---|---|
| role_permissions | `role_id` | roles(`id`) | ON DELETE CASCADE |
| role_permissions | `permission_id` | permissions(`id`) | ON DELETE CASCADE |
| users | `role_id` | roles(`id`) | ON DELETE RESTRICT |
| password_resets | `user_id` | users(`id`) | ON DELETE CASCADE |
| remember_tokens | `user_id` | users(`id`) | ON DELETE CASCADE |
| sections | `class_id` | classes(`id`) | ON DELETE CASCADE |
| students | `user_id` | users(`id`) | ON DELETE SET NULL |
| students | `class_id` | classes(`id`) | ON DELETE SET NULL |
| students | `section_id` | sections(`id`) | ON DELETE SET NULL |
| teachers | `user_id` | users(`id`) | ON DELETE SET NULL |
| parents | `user_id` | users(`id`) | ON DELETE SET NULL |
| student_parents | `student_id` | students(`id`) | ON DELETE CASCADE |
| student_parents | `parent_id` | parents(`id`) | ON DELETE CASCADE |
| attendance | `student_id` | students(`id`) | ON DELETE CASCADE |
| teacher_attendance | `teacher_id` | teachers(`id`) | ON DELETE CASCADE |
| results | `exam_id` | exams(`id`) | ON DELETE CASCADE |
| results | `student_id` | students(`id`) | ON DELETE CASCADE |
| results | `subject_id` | subjects(`id`) | ON DELETE CASCADE |
| fees | `student_id` | students(`id`) | ON DELETE CASCADE |
| payments | `fee_id` | fees(`id`) | ON DELETE CASCADE |
| payments | `student_id` | students(`id`) | ON DELETE CASCADE |
| staff | `user_id` | users(`id`) | ON DELETE SET NULL |
| vehicles | `driver_id` | drivers(`id`) | ON DELETE SET NULL |
| inventory_items | `category_id` | inventory_categories(`id`) | ON DELETE SET NULL |
| inventory_items | `supplier_id` | suppliers(`id`) | ON DELETE SET NULL |
| messages | `sender_id` | users(`id`) | ON DELETE CASCADE |
| messages | `receiver_id` | users(`id`) | ON DELETE CASCADE |
| notifications | `user_id` | users(`id`) | ON DELETE CASCADE |
| discipline_records | `student_id` | students(`id`) | ON DELETE CASCADE |
| student_marks | `student_id` | students(`id`) | ON DELETE CASCADE |
| student_marks | `subject_id` | subjects(`id`) | ON DELETE CASCADE |
| periodic_bulletins | `student_id` | students(`id`) | ON DELETE CASCADE |
| bulletin_audit_log | `bulletin_id` | periodic_bulletins(`id`) | ON DELETE CASCADE |
| user_establishments | `user_id` | users(`id`) | ON DELETE CASCADE |
| user_establishments | `establishment_id` | establishments(`id`) | ON DELETE CASCADE |
| student_enrollments | `student_id` | students(`id`) | ON DELETE CASCADE |
| student_enrollments | `class_id` | classes(`id`) |  |
| student_enrollments | `establishment_id` | establishments(`id`) |  |
| development_assessments | `student_id` | students(`id`) | ON DELETE CASCADE |
| development_assessments | `domain_id` | development_domains(`id`) |  |
| child_health_records | `student_id` | students(`id`) | ON DELETE CASCADE |
| authorized_pickups | `student_id` | students(`id`) | ON DELETE CASCADE |
| national_exam_registrations | `student_id` | students(`id`) | ON DELETE CASCADE |
| retake_exams | `student_id` | students(`id`) | ON DELETE CASCADE |
| conduct_grades | `student_id` | students(`id`) | ON DELETE CASCADE |
| fee_plans | `establishment_id` | establishments(`id`) |  |