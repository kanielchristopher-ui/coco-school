-- 0001 : schéma de référence (état de l'application existante après audit)
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;












































-- =====================================================================
-- ROLES & USERS (Authentication / RBAC)
-- =====================================================================

CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(60) NOT NULL,
  `slug` VARCHAR(60) NOT NULL UNIQUE,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `module` VARCHAR(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_role_permission` (`role_id`, `permission_id`),
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` INT UNSIGNED NOT NULL,
  `username` VARCHAR(60) NOT NULL UNIQUE,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(60) DEFAULT NULL,
  `last_name` VARCHAR(60) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active','inactive','suspended','pending') NOT NULL DEFAULT 'active',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_users_role` (`role_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pr_email` (`email`),
  CONSTRAINT `fk_pr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `selector` VARCHAR(64) NOT NULL,
  `token_hash` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_selector` (`selector`),
  CONSTRAINT `fk_rt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- ACADEMIC STRUCTURE
-- =====================================================================

CREATE TABLE IF NOT EXISTS `academic_sessions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(60) NOT NULL,
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `is_current` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `classes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(60) NOT NULL,
  `numeric_name` VARCHAR(10) DEFAULT NULL COMMENT 'Rang du niveau dans le cycle (1,2,3...)',
  `cycle` ENUM('maternel','primaire','secondaire') NOT NULL DEFAULT 'primaire',
  `class_teacher_id` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_class_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sections` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(30) NOT NULL,
  `room_no` VARCHAR(20) DEFAULT NULL,
  `capacity` INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_section` (`class_id`, `name`),
  CONSTRAINT `fk_section_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subjects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `code` VARCHAR(20) DEFAULT NULL,
  `class_id` INT UNSIGNED DEFAULT NULL,
  `teacher_id` INT UNSIGNED DEFAULT NULL,
  `credits` INT UNSIGNED DEFAULT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_subject_class` (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `timetables` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_id` INT UNSIGNED NOT NULL,
  `section_id` INT UNSIGNED DEFAULT NULL,
  `day_of_week` TINYINT UNSIGNED NOT NULL COMMENT '1=Mon ... 7=Sun',
  `period` VARCHAR(30) DEFAULT NULL,
  `subject_id` INT UNSIGNED DEFAULT NULL,
  `teacher_id` INT UNSIGNED DEFAULT NULL,
  `start_time` TIME DEFAULT NULL,
  `end_time` TIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tt_class` (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- PEOPLE
-- =====================================================================

CREATE TABLE IF NOT EXISTS `students` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `official_id` VARCHAR(20) DEFAULT NULL,
  `admission_no` VARCHAR(30) NOT NULL UNIQUE,
  `roll_no` VARCHAR(20) DEFAULT NULL,
  `first_name` VARCHAR(60) NOT NULL,
  `middle_name` VARCHAR(60) DEFAULT NULL,
  `last_name` VARCHAR(60) DEFAULT NULL,
  `gender` ENUM('male','female','other') DEFAULT 'male',
  `dob` DATE DEFAULT NULL,
  `birth_place` VARCHAR(100) DEFAULT NULL,
  `nationality` VARCHAR(60) DEFAULT 'Congolaise',
  `perm_number` VARCHAR(30) DEFAULT NULL COMMENT 'Numéro PERM (identifiant national de l''élève)',
  `class_id` INT UNSIGNED DEFAULT NULL,
  `section_id` INT UNSIGNED DEFAULT NULL,
  `guardian_name` VARCHAR(100) DEFAULT NULL,
  `guardian_relation` VARCHAR(40) DEFAULT NULL,
  `guardian_phone` VARCHAR(30) DEFAULT NULL,
  `guardian_email` VARCHAR(120) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(60) DEFAULT NULL,
  `state` VARCHAR(60) DEFAULT NULL,
  `zip` VARCHAR(15) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(120) DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `blood_group` VARCHAR(10) DEFAULT NULL,
  `medical_info` TEXT,
  `status` ENUM('active','inactive','alumni','transferred') NOT NULL DEFAULT 'active',
  `admission_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_student_class` (`class_id`),
  KEY `idx_student_section` (`section_id`),
  CONSTRAINT `fk_student_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_student_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_student_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `teachers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `official_id` VARCHAR(20) DEFAULT NULL,
  `employee_id` VARCHAR(30) NOT NULL UNIQUE,
  `first_name` VARCHAR(60) NOT NULL,
  `last_name` VARCHAR(60) DEFAULT NULL,
  `gender` ENUM('male','female','other') DEFAULT 'male',
  `dob` DATE DEFAULT NULL,
  `department` VARCHAR(80) DEFAULT NULL,
  `teaching_level` ENUM('maternel','primaire','secondaire') NOT NULL DEFAULT 'secondaire',
  `qualification` VARCHAR(255) DEFAULT NULL,
  `specialization` VARCHAR(120) DEFAULT NULL,
  `experience_years` INT UNSIGNED DEFAULT 0,
  `phone` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(120) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `salary` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `joining_date` DATE DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `cv` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active','inactive','left') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_teacher_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `parents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `official_id` VARCHAR(20) DEFAULT NULL,
  `father_name` VARCHAR(100) DEFAULT NULL,
  `mother_name` VARCHAR(100) DEFAULT NULL,
  `guardian_name` VARCHAR(100) DEFAULT NULL,
  `occupation` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(120) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_parent_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `student_parents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `parent_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sp` (`student_id`, `parent_id`),
  CONSTRAINT `fk_sp_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sp_parent` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- ATTENDANCE
-- =====================================================================

CREATE TABLE IF NOT EXISTS `attendance` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `class_id` INT UNSIGNED DEFAULT NULL,
  `section_id` INT UNSIGNED DEFAULT NULL,
  `subject_id` INT UNSIGNED DEFAULT NULL,
  `date` DATE NOT NULL,
  `status` ENUM('present','absent','late','leave') NOT NULL DEFAULT 'present',
  `remark` VARCHAR(255) DEFAULT NULL,
  `taken_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_att_student_date_subject` (`student_id`, `date`, `subject_id`),
  CONSTRAINT `fk_att_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `teacher_attendance` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `teacher_id` INT UNSIGNED NOT NULL,
  `date` DATE NOT NULL,
  `status` ENUM('present','absent','late','leave') NOT NULL DEFAULT 'present',
  `remark` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ta_teacher_date` (`teacher_id`, `date`),
  CONSTRAINT `fk_ta_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- EXAMS & RESULTS
-- =====================================================================

CREATE TABLE IF NOT EXISTS `exam_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(60) NOT NULL UNIQUE,
  `description` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `grades` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `grade` VARCHAR(10) NOT NULL,
  `min_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `max_percent` DECIMAL(5,2) NOT NULL DEFAULT 100.00,
  `point` DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  `remarks` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_grade_name` (`grade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `exams` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `exam_type_id` INT UNSIGNED DEFAULT NULL,
  `subject_id` INT UNSIGNED DEFAULT NULL,
  `class_id` INT UNSIGNED DEFAULT NULL,
  `section_id` INT UNSIGNED DEFAULT NULL,
  `session_id` INT UNSIGNED DEFAULT NULL,
  `term` VARCHAR(30) DEFAULT NULL,
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `status` ENUM('draft','published','upcoming','active','completed') NOT NULL DEFAULT 'upcoming',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_exam_class` (`class_id`),
  KEY `idx_exam_subject` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `results` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `exam_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `subject_id` INT UNSIGNED NOT NULL,
  `marks_obtained` DECIMAL(6,2) NOT NULL DEFAULT 0,
  `max_marks` DECIMAL(6,2) NOT NULL DEFAULT 100,
  `grade` VARCHAR(5) DEFAULT NULL,
  `remarks` VARCHAR(255) DEFAULT NULL,
  `teacher_comments` VARCHAR(255) DEFAULT NULL,
  `correction_comment` VARCHAR(500) DEFAULT NULL,
  `status` ENUM('draft','submitted','approved','returned','published','locked') NOT NULL DEFAULT 'draft',
  `published_at` DATETIME DEFAULT NULL,
  `reopened_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_result` (`exam_id`, `student_id`, `subject_id`),
  CONSTRAINT `fk_res_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_res_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_res_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `promotions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `from_class_id` INT UNSIGNED DEFAULT NULL,
  `to_class_id` INT UNSIGNED DEFAULT NULL,
  `session_id` INT UNSIGNED DEFAULT NULL,
  `promoted_date` DATE DEFAULT NULL,
  `remarks` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_promo_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- FEES & PAYROLL
-- =====================================================================

CREATE TABLE IF NOT EXISTS `fee_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `frequency` ENUM('one_time','monthly','yearly','term') NOT NULL DEFAULT 'monthly',
  `description` VARCHAR(255) DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fees` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_no` VARCHAR(40) NOT NULL UNIQUE,
  `student_id` INT UNSIGNED NOT NULL,
  `fee_type_id` INT UNSIGNED DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `discount_type` ENUM('fixed','percent','none') NOT NULL DEFAULT 'none',
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `scholarship` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `due_date` DATE DEFAULT NULL,
  `status` ENUM('pending','partial','paid','overdue','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fee_student` (`student_id`),
  CONSTRAINT `fk_fee_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `receipt_no` VARCHAR(40) NOT NULL UNIQUE,
  `fee_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` ENUM('cash','card','bank_transfer','cheque','online','mobile_money') NOT NULL DEFAULT 'cash',
  `transaction_id` VARCHAR(100) DEFAULT NULL,
  `paid_by` INT UNSIGNED DEFAULT NULL,
  `payment_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `remark` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pay_fee` (`fee_id`),
  CONSTRAINT `fk_pay_fee` FOREIGN KEY (`fee_id`) REFERENCES `fees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fee_discounts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(100) DEFAULT NULL,
  `discount_type` ENUM('fixed','percent') NOT NULL DEFAULT 'fixed',
  `value` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `reason` VARCHAR(255) DEFAULT NULL,
  `valid_from` DATE DEFAULT NULL,
  `valid_to` DATE DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fd_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `staff` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `employee_id` VARCHAR(30) NOT NULL UNIQUE,
  `first_name` VARCHAR(60) NOT NULL,
  `last_name` VARCHAR(60) DEFAULT NULL,
  `department` VARCHAR(80) DEFAULT NULL,
  `designation` VARCHAR(80) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(120) DEFAULT NULL,
  `joining_date` DATE DEFAULT NULL,
  `basic_salary` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('active','inactive','left') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_staff_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `salary` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `staff_id` INT UNSIGNED DEFAULT NULL COMMENT 'For non-teaching staff',
  `teacher_id` INT UNSIGNED DEFAULT NULL COMMENT 'For teaching staff',
  `month` TINYINT UNSIGNED NOT NULL,
  `year` SMALLINT UNSIGNED NOT NULL,
  `basic_salary` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `allowances` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `deductions` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `net_salary` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `payment_date` DATE DEFAULT NULL,
  `status` ENUM('paid','pending','draft') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_salary_month` (`month`, `year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- TRANSPORT
-- =====================================================================

CREATE TABLE IF NOT EXISTS `drivers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `license_no` VARCHAR(60) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_no` VARCHAR(30) NOT NULL UNIQUE,
  `model` VARCHAR(80) DEFAULT NULL,
  `capacity` INT UNSIGNED DEFAULT NULL,
  `driver_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('active','inactive','maintenance') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_veh_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `routes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `stops` TEXT,
  `fare` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transport_assignments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `route_id` INT UNSIGNED DEFAULT NULL,
  `vehicle_id` INT UNSIGNED DEFAULT NULL,
  `pickup_point` VARCHAR(120) DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `idx_ta_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- INVENTORY
-- =====================================================================

CREATE TABLE IF NOT EXISTS `inventory_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `contact_person` VARCHAR(80) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(120) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventory_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `supplier_id` INT UNSIGNED DEFAULT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 0,
  `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `purchase_date` DATE DEFAULT NULL,
  `location` VARCHAR(120) DEFAULT NULL,
  `status` ENUM('in_stock','low_stock','out_of_stock') NOT NULL DEFAULT 'in_stock',
  PRIMARY KEY (`id`),
  KEY `idx_inv_cat` (`category_id`),
  CONSTRAINT `fk_inv_cat` FOREIGN KEY (`category_id`) REFERENCES `inventory_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_inv_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `purchases` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_id` INT UNSIGNED NOT NULL,
  `supplier_id` INT UNSIGNED DEFAULT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `invoice_no` VARCHAR(40) DEFAULT NULL,
  `purchase_date` DATE DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pur_item` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `stock_issues` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_id` INT UNSIGNED NOT NULL,
  `issued_to` VARCHAR(120) DEFAULT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `issue_date` DATE DEFAULT NULL,
  `remark` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_issue_item` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- EVENTS, MESSAGES, NOTIFICATIONS, LOGS, SETTINGS
-- =====================================================================

CREATE TABLE IF NOT EXISTS `events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `event_date` DATE NOT NULL,
  `event_time` TIME DEFAULT NULL,
  `event_type` ENUM('event','meeting','holiday','exam','sport') NOT NULL DEFAULT 'event',
  `location` VARCHAR(120) DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `holidays` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sender_id` INT UNSIGNED NOT NULL,
  `receiver_id` INT UNSIGNED NOT NULL,
  `subject` VARCHAR(150) DEFAULT NULL,
  `body` TEXT,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` DATETIME DEFAULT NULL,
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_msg_sender` (`sender_id`),
  KEY `idx_msg_receiver` (`receiver_id`),
  CONSTRAINT `fk_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT,
  `link` VARCHAR(255) DEFAULT NULL,
  `type` VARCHAR(40) DEFAULT 'info',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(80) NOT NULL,
  `module` VARCHAR(60) DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_log_user` (`user_id`),
  KEY `idx_log_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(80) NOT NULL UNIQUE,
  `setting_value` TEXT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================

-- ---------------------------------------------------------------------
-- DISCIPLINE : incidents et sanctions (directeur de discipline)
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `discipline_records` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `incident_date` DATE NOT NULL,
  `type` VARCHAR(60) NOT NULL,
  `severity` ENUM('minor','moderate','serious') NOT NULL DEFAULT 'minor',
  `description` TEXT DEFAULT NULL,
  `sanction` VARCHAR(100) DEFAULT NULL,
  `parent_notified` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('open','resolved') NOT NULL DEFAULT 'open',
  `recorded_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_discipline_student` (`student_id`),
  KEY `idx_discipline_status` (`status`),
  CONSTRAINT `fk_discipline_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Compteurs des identifiants officiels (ELV-0001, ENS-0001, PAR-0001)
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `official_id_counters` (
  `prefix` VARCHAR(10) NOT NULL,
  `last_number` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`prefix`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- MODULE EXAMENS / RÉSULTATS (migration intégrée)
-- ---------------------------------------------------------------------

-- ============================================================
--  Exam & Result Management Module - Migration
--  Adds the grades scale table, section/subject assignment for
--  exams, the extended exam status set (Upcoming / Active /
--  Completed) and teacher comments on results.
--
--  Safe to run on both fresh (schema.sql) and existing databases.
-- ============================================================

-- ------------------------------------------------------------
-- 1. grades table
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `grades` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `grade` VARCHAR(10) NOT NULL,
  `min_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `max_percent` DECIMAL(5,2) NOT NULL DEFAULT 100.00,
  `point` DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  `remarks` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_grade_name` (`grade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. exams - subject + section assignment, term, statuses
-- ------------------------------------------------------------
SET @has_subject_id = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exams' AND COLUMN_NAME = 'subject_id');

SET @sql = IF(@has_subject_id = 0,
  'ALTER TABLE `exams` ADD COLUMN `subject_id` INT UNSIGNED DEFAULT NULL AFTER `exam_type_id`',
  'SELECT 1');

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

SET @has_section_id = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exams' AND COLUMN_NAME = 'section_id');

SET @sql = IF(@has_section_id = 0,
  'ALTER TABLE `exams` ADD COLUMN `section_id` INT UNSIGNED DEFAULT NULL AFTER `class_id`',
  'SELECT 1');

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

SET @has_term = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exams' AND COLUMN_NAME = 'term');

SET @sql = IF(@has_term = 0,
  'ALTER TABLE `exams` ADD COLUMN `term` VARCHAR(30) DEFAULT NULL AFTER `session_id`',
  'SELECT 1');

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

ALTER TABLE `exams`
  MODIFY `status` ENUM('draft','published','upcoming','active','completed')
  NOT NULL DEFAULT 'upcoming';

-- Map existing statuses onto the new set.
UPDATE `exams` SET `status` = 'upcoming'  WHERE `status` = 'draft';

UPDATE `exams` SET `status` = 'completed' WHERE `status` = 'published';

-- ------------------------------------------------------------
-- 3. results - extra column for the full report card
-- ------------------------------------------------------------
SET @has_teacher_comments = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'results' AND COLUMN_NAME = 'teacher_comments');

SET @sql = IF(@has_teacher_comments = 0,
  'ALTER TABLE `results` ADD COLUMN `teacher_comments` VARCHAR(255) DEFAULT NULL AFTER `remarks`',
  'SELECT 1');

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- 4. students - middle name (patronymic)
-- ------------------------------------------------------------
SET @has_middle_name = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'students' AND COLUMN_NAME = 'middle_name');

SET @sql = IF(@has_middle_name = 0,
  'ALTER TABLE `students` ADD COLUMN `middle_name` VARCHAR(60) DEFAULT NULL AFTER `first_name`',
  'SELECT 1');

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------
-- MODULE FINANCE (frais, salaires, dépenses)
-- ---------------------------------------------------------------------
-- ============================================================
-- School Finance module (addition only)
-- New tables only. No existing tables are modified.
-- ============================================================

CREATE TABLE IF NOT EXISTS student_fees (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED NOT NULL,
    class_id INT UNSIGNED NULL,
    fee_type VARCHAR(40) NOT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    academic_session VARCHAR(60) NOT NULL,
    month VARCHAR(20) NULL,
    due_date DATE NULL,
    status ENUM('pending','partial','paid') NOT NULL DEFAULT 'pending',
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_sf_student (student_id),
    KEY idx_sf_class (class_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS fee_collections (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED NOT NULL,
    class_id INT UNSIGNED NULL,
    fee_type VARCHAR(40) NOT NULL,
    amount_paid DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(20) NOT NULL DEFAULT 'cash',
    collected_by INT UNSIGNED NULL,
    notes VARCHAR(255) NULL,
    receipt_no VARCHAR(30) NOT NULL,
    status ENUM('active','voided') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_receipt_no (receipt_no),
    KEY idx_fc_student (student_id),
    KEY idx_fc_class (class_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payrolls (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    employee_type ENUM('teacher','staff') NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    position VARCHAR(80) NULL,
    salary_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    month VARCHAR(7) NOT NULL,
    payment_date DATE NOT NULL,
    status ENUM('paid','pending') NOT NULL DEFAULT 'pending',
    notes VARCHAR(255) NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pay_emp (employee_type, employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS expense_categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(80) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_expcat_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS expenses (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    expense_date DATE NOT NULL,
    paid_by INT UNSIGNED NULL,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_exp_cat (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- BULLETINS PÉRIODIQUES (moteur issu de Coco School)
--  school_evaluation_schemes : schémas d'évaluation versionnés (3 trimestres / 2 semestres)
--  student_marks             : points par élève, cours et étape (période, examen)
--  periodic_bulletins        : bulletins versionnés (draft > validated > published > superseded)
--  bulletin_audit_log        : journal de chaque action
--  school_extensions         : modèles .ccplg signés installés
-- ---------------------------------------------------------------------





CREATE TABLE IF NOT EXISTS `school_extensions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `plugin_id` VARCHAR(80) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `version` VARCHAR(30) NOT NULL,
  `package_sha256` CHAR(64) NOT NULL,
  `manifest_json` LONGTEXT DEFAULT NULL,
  `installed_by` INT UNSIGNED DEFAULT NULL,
  `installed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('active','disabled') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_school_plugin` (`school_id`, `plugin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `school_evaluation_schemes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `level_code` VARCHAR(30) NOT NULL COMMENT 'primaire (maternel + primaire) ou secondaire',
  `academic_year_id` INT UNSIGNED NOT NULL,
  `scheme_id` VARCHAR(80) NOT NULL,
  `version` INT UNSIGNED NOT NULL DEFAULT 1,
  `config_json` LONGTEXT NOT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_school_level_year_version` (`school_id`, `level_code`, `academic_year_id`, `version`),
  KEY `idx_scheme_year` (`academic_year_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `student_marks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `student_id` INT UNSIGNED NOT NULL,
  `class_id` INT UNSIGNED NOT NULL,
  `subject_id` INT UNSIGNED NOT NULL,
  `academic_year_id` INT UNSIGNED NOT NULL,
  `scheme_version_id` INT UNSIGNED NOT NULL,
  `step_id` VARCHAR(40) NOT NULL,
  `max_points` DECIMAL(8,2) NOT NULL,
  `points_obtained` DECIMAL(8,2) NOT NULL,
  `entered_by` INT UNSIGNED DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_student_subject_step` (`student_id`, `subject_id`, `academic_year_id`, `step_id`),
  KEY `idx_marks_class_year` (`class_id`, `academic_year_id`),
  CONSTRAINT `fk_marks_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_marks_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `periodic_bulletins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `student_id` INT UNSIGNED NOT NULL,
  `class_id` INT UNSIGNED NOT NULL,
  `academic_year_id` INT UNSIGNED NOT NULL,
  `scheme_version_id` INT UNSIGNED NOT NULL,
  `template_id` VARCHAR(120) NOT NULL DEFAULT 'classique',
  `period_id` VARCHAR(40) NOT NULL COMMENT 'T1, T2, T3, S1, S2 ou YEAR',
  `version` INT UNSIGNED NOT NULL DEFAULT 1,
  `status` ENUM('draft','validated','published','superseded') NOT NULL DEFAULT 'draft',
  `snapshot_json` LONGTEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `validated_by` INT UNSIGNED DEFAULT NULL,
  `validated_at` DATETIME DEFAULT NULL,
  `published_by` INT UNSIGNED DEFAULT NULL,
  `published_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_student_period_version` (`student_id`, `academic_year_id`, `period_id`, `version`),
  KEY `idx_bulletin_published` (`student_id`, `status`, `academic_year_id`),
  KEY `idx_bulletin_class` (`class_id`, `academic_year_id`, `period_id`),
  CONSTRAINT `fk_bulletin_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bulletin_audit_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `bulletin_id` INT UNSIGNED NOT NULL,
  `actor_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(40) NOT NULL,
  `details_json` LONGTEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bulletin_audit` (`bulletin_id`, `created_at`),
  CONSTRAINT `fk_audit_bulletin` FOREIGN KEY (`bulletin_id`) REFERENCES `periodic_bulletins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
