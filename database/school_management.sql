-- COCO SCHOOL — installation complète en UN fichier (migrations 0001..N + données de démonstration).
-- Production : appliquer database/migrations/*.sql (php tools/migrate.php migrate) SANS les fichiers de database/seeds/.
-- Fichier généré par tools/build_sql.py : ne pas modifier à la main.
SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS `school_management` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `school_management`;


-- ===== database/migrations/0001_baseline_schema.sql =====
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


-- ===== database/migrations/0002_reference_data.sql =====
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


-- ===== database/migrations/0003_core_audit_log.sql =====
-- 0003 : journal d'audit du Core

-- Journal d'audit du Core (CocoCore\Database\PdoAuditLog)
CREATE TABLE IF NOT EXISTS `core_audit_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `actor_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(60) NOT NULL,
  `entity` VARCHAR(60) NOT NULL,
  `entity_id` INT UNSIGNED DEFAULT NULL,
  `details_json` LONGTEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_core_audit_entity` (`entity`, `entity_id`),
  KEY `idx_core_audit_actor` (`actor_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ===== database/migrations/0004_levels_enrollment_licensing.sql =====
-- 0004 : séparation maternel / primaire / secondaire, inscriptions par année, licences
-- Sources : structure du système éducatif RDC (maternel 3 ans non obligatoire ; primaire 6 ans, admission à 6 ans révolus, TENAFEP ;
-- secondaire : cycle long de 6 ans = 2 ans d'éducation de base + 4 ans d'humanités, Exetat). Voir documentation/NIVEAUX.md.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============ ÉTABLISSEMENTS : une gestion DISTINCTE par niveau ============
CREATE TABLE IF NOT EXISTS `establishments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(30) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `level` ENUM('maternel','primaire','secondaire') NOT NULL,
  `regime` ENUM('public','conventionne_catholique','conventionne_protestant','conventionne_kimbanguiste','conventionne_autre','prive') NOT NULL DEFAULT 'prive',
  `head_title` VARCHAR(60) NOT NULL DEFAULT 'Directeur',
  `head_name` VARCHAR(120) DEFAULT NULL,
  `province` VARCHAR(80) DEFAULT NULL,
  `city` VARCHAR(80) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(120) DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_establishment_code` (`code`),
  KEY `idx_establishment_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_establishments` (
  `user_id` INT UNSIGNED NOT NULL,
  `establishment_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`, `establishment_id`),
  CONSTRAINT `fk_ue_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ue_establishment` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `establishments` (`id`, `code`, `name`, `level`, `regime`, `head_title`) VALUES
(1, 'MAT-01', 'École maternelle', 'maternel', 'prive', 'Directeur'),
(2, 'PRI-01', 'École primaire', 'primaire', 'prive', 'Directeur'),
(3, 'SEC-01', 'Institut secondaire', 'secondaire', 'prive', 'Préfet');

ALTER TABLE `classes` ADD COLUMN `establishment_id` INT UNSIGNED DEFAULT NULL AFTER `cycle`, ADD KEY `idx_class_establishment` (`establishment_id`);
UPDATE `classes` SET `establishment_id` = CASE `cycle` WHEN 'maternel' THEN 1 WHEN 'primaire' THEN 2 ELSE 3 END;

-- ============ OPTIONS / FILIÈRES (secondaire) ============
CREATE TABLE IF NOT EXISTS `school_options` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(20) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `group_name` VARCHAR(120) NOT NULL,
  `exetat_code` VARCHAR(20) DEFAULT NULL COMMENT 'code de l\'option à l\'Examen d\'État (à renseigner selon la nomenclature officielle)',
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_option_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `school_options` (`code`, `name`, `group_name`) VALUES
('BC', 'Biologie-Chimie', 'Enseignement général — scientifique'),
('MP', 'Mathématiques-Physique', 'Enseignement général — scientifique'),
('LI', 'Littéraire', 'Enseignement général — littéraire'),
('PG', 'Pédagogie générale', 'Enseignement pédagogique'),
('CG', 'Commerciale et gestion', 'Enseignement technique — commerciale et administrative'),
('EL', 'Électricité', 'Enseignement technique — industrielle'),
('MG', 'Mécanique générale', 'Enseignement technique — industrielle'),
('CO', 'Construction', 'Enseignement technique — industrielle'),
('AG', 'Agronomie générale', 'Enseignement technique — agricole'),
('VE', 'Vétérinaire', 'Enseignement technique — vétérinaire'),
('SO', 'Sociale (nutrition, santé)', 'Enseignement technique — sociale'),
('CC', 'Coupe et couture', 'Enseignement professionnel'),
('SE', 'Secrétariat-administration', 'Enseignement technique — commerciale et administrative');

ALTER TABLE `classes` ADD COLUMN `option_id` INT UNSIGNED DEFAULT NULL AFTER `establishment_id`, ADD KEY `idx_class_option` (`option_id`);
UPDATE `classes` SET `option_id` = (SELECT `id` FROM `school_options` WHERE `code` = 'BC') WHERE `cycle` = 'secondaire' AND `name` LIKE '%Humanités Biologie-Chimie%';
UPDATE `classes` SET `option_id` = (SELECT `id` FROM `school_options` WHERE `code` = 'MP') WHERE `cycle` = 'secondaire' AND `name` LIKE '%Humanités Mathématiques-Physique%';
UPDATE `classes` SET `option_id` = (SELECT `id` FROM `school_options` WHERE `code` = 'LI') WHERE `cycle` = 'secondaire' AND `name` LIKE '%Humanités Littéraire%';
UPDATE `classes` SET `option_id` = (SELECT `id` FROM `school_options` WHERE `code` = 'PG') WHERE `cycle` = 'secondaire' AND `name` LIKE '%Humanités Pédagogie générale%';
UPDATE `classes` SET `option_id` = (SELECT `id` FROM `school_options` WHERE `code` = 'CG') WHERE `cycle` = 'secondaire' AND `name` LIKE '%Humanités Commerciale et gestion%';
UPDATE `classes` SET `option_id` = (SELECT `id` FROM `school_options` WHERE `code` = 'EL') WHERE `cycle` = 'secondaire' AND `name` LIKE '%Humanités Électricité%';

-- Maxima par cours (secondaire) : NULL = valeur par défaut calculée par le Core (CourseMaxima) à partir des heures hebdomadaires
ALTER TABLE `subjects`
  ADD COLUMN `weekly_hours` TINYINT UNSIGNED DEFAULT NULL AFTER `credits`,
  ADD COLUMN `period_max` DECIMAL(5,1) DEFAULT NULL AFTER `weekly_hours`,
  ADD COLUMN `exam_max` DECIMAL(5,1) DEFAULT NULL AFTER `period_max`,
  ADD COLUMN `coefficient` DECIMAL(4,2) NOT NULL DEFAULT 1.00 AFTER `exam_max`;
UPDATE `subjects` SET `weekly_hours` = `credits` WHERE `credits` IS NOT NULL;

-- ============ INSCRIPTIONS PAR ANNÉE SCOLAIRE ============
CREATE TABLE IF NOT EXISTS `student_enrollments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `class_id` INT UNSIGNED NOT NULL,
  `section_id` INT UNSIGNED DEFAULT NULL,
  `establishment_id` INT UNSIGNED NOT NULL,
  `academic_year_id` INT UNSIGNED NOT NULL,
  `enrolled_on` DATE NOT NULL,
  `status` ENUM('enrolled','promoted','repeating','retake','transferred','withdrawn','graduated') NOT NULL DEFAULT 'enrolled',
  `final_percent` DECIMAL(5,2) DEFAULT NULL,
  `decision_note` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_enrollment_student_year` (`student_id`, `academic_year_id`),
  KEY `idx_enrollment_class_year` (`class_id`, `academic_year_id`),
  CONSTRAINT `fk_enrollment_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_enrollment_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  CONSTRAINT `fk_enrollment_establishment` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============ MATERNEL : évaluation qualitative, santé, sécurité, cahier de liaison ============
CREATE TABLE IF NOT EXISTS `development_domains` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(20) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_domain_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `development_domains` (`code`, `name`, `description`, `sort_order`) VALUES
('LANG', 'Langage et communication', 'Expression orale, vocabulaire, écoute, pré-lecture', 1),
('MATH', 'Éveil mathématique', 'Numération, formes, comparaisons, classements', 2),
('SCI', 'Éveil scientifique et découverte du monde', 'Observation, environnement, santé et hygiène', 3),
('SOC', 'Développement socio-affectif', 'Vie en groupe, autonomie, respect des règles', 4),
('MOT', 'Psychomotricité et éducation physique', 'Motricité globale et fine, graphisme, coordination', 5),
('ART', 'Expression artistique', 'Dessin, chant, rythme, modelage', 6),
('MOR', 'Éveil moral et religieux', 'Valeurs, civilité, politesse', 7);

CREATE TABLE IF NOT EXISTS `development_assessments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `domain_id` INT UNSIGNED NOT NULL,
  `academic_year_id` INT UNSIGNED NOT NULL,
  `period_id` VARCHAR(10) NOT NULL,
  `level` ENUM('acquired','in_progress','not_acquired') NOT NULL,
  `comment` VARCHAR(255) DEFAULT NULL,
  `assessed_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dev_assessment` (`student_id`, `domain_id`, `academic_year_id`, `period_id`),
  CONSTRAINT `fk_devass_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_devass_domain` FOREIGN KEY (`domain_id`) REFERENCES `development_domains` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `child_health_records` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `vaccinations` TEXT DEFAULT NULL,
  `allergies` TEXT DEFAULT NULL,
  `medical_notes` TEXT DEFAULT NULL,
  `doctor_name` VARCHAR(120) DEFAULT NULL,
  `doctor_phone` VARCHAR(30) DEFAULT NULL,
  `emergency_contact_name` VARCHAR(120) DEFAULT NULL,
  `emergency_contact_phone` VARCHAR(30) DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_health_student` (`student_id`),
  CONSTRAINT `fk_health_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `authorized_pickups` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `full_name` VARCHAR(120) NOT NULL,
  `relation` VARCHAR(60) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `id_document` VARCHAR(60) DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pickup_student` (`student_id`),
  CONSTRAINT `fk_pickup_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pickup_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `picked_by_name` VARCHAR(120) NOT NULL,
  `authorized` TINYINT(1) NOT NULL,
  `recorded_by` INT UNSIGNED DEFAULT NULL,
  `picked_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pickuplog_student` (`student_id`, `picked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `daily_reports` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_id` INT UNSIGNED NOT NULL,
  `section_id` INT UNSIGNED DEFAULT NULL,
  `report_date` DATE NOT NULL,
  `activities` TEXT DEFAULT NULL,
  `meals` VARCHAR(255) DEFAULT NULL,
  `nap_minutes` SMALLINT UNSIGNED DEFAULT NULL,
  `notes_for_parents` TEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_daily_report` (`class_id`, `section_id`, `report_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============ PRIMAIRE / SECONDAIRE : examens nationaux, repêchage, conduite ============
CREATE TABLE IF NOT EXISTS `national_exam_registrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `exam_code` ENUM('TENAFEP','EXETAT') NOT NULL,
  `academic_year_id` INT UNSIGNED NOT NULL,
  `candidate_number` VARCHAR(40) DEFAULT NULL,
  `center` VARCHAR(120) DEFAULT NULL,
  `option_id` INT UNSIGNED DEFAULT NULL,
  `school_percent` DECIMAL(5,2) DEFAULT NULL,
  `exam_percent` DECIMAL(5,2) DEFAULT NULL,
  `final_percent` DECIMAL(5,2) DEFAULT NULL,
  `result` ENUM('pending','passed','failed','absent') NOT NULL DEFAULT 'pending',
  `mention` VARCHAR(40) DEFAULT NULL,
  `registered_on` DATE NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_national_exam` (`student_id`, `exam_code`, `academic_year_id`),
  UNIQUE KEY `uq_candidate_number` (`candidate_number`),
  CONSTRAINT `fk_nat_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `retake_exams` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `subject_id` INT UNSIGNED DEFAULT NULL,
  `academic_year_id` INT UNSIGNED NOT NULL,
  `original_percent` DECIMAL(5,2) NOT NULL,
  `retake_percent` DECIMAL(5,2) DEFAULT NULL,
  `result` ENUM('pending','passed','failed') NOT NULL DEFAULT 'pending',
  `exam_date` DATE DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_retake_student` (`student_id`, `academic_year_id`),
  CONSTRAINT `fk_retake_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `conduct_grades` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `academic_year_id` INT UNSIGNED NOT NULL,
  `period_id` VARCHAR(10) NOT NULL,
  `score` DECIMAL(5,2) NOT NULL,
  `mention` VARCHAR(40) DEFAULT NULL,
  `incident_count` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `comment` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_conduct` (`student_id`, `academic_year_id`, `period_id`),
  CONSTRAINT `fk_conduct_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============ FINANCE : barèmes de frais par établissement ============
CREATE TABLE IF NOT EXISTS `fee_plans` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `establishment_id` INT UNSIGNED NOT NULL,
  `academic_year_id` INT UNSIGNED NOT NULL,
  `class_id` INT UNSIGNED DEFAULT NULL,
  `fee_type` VARCHAR(80) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `frequency` ENUM('one_time','monthly','term','semester','yearly') NOT NULL DEFAULT 'term',
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_feeplan_est_year` (`establishment_id`, `academic_year_id`),
  CONSTRAINT `fk_feeplan_establishment` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============ LICENCES ============
CREATE TABLE IF NOT EXISTS `licenses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `license_id` VARCHAR(64) NOT NULL,
  `token` LONGTEXT NOT NULL,
  `holder` VARCHAR(200) NOT NULL,
  `valid_from` DATE NOT NULL,
  `valid_until` DATE NOT NULL,
  `installed_by` INT UNSIGNED DEFAULT NULL,
  `installed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('active','replaced','revoked') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_license_id` (`license_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `license_events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event` VARCHAR(40) NOT NULL,
  `details_json` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `system_state` (
  `key_name` VARCHAR(60) NOT NULL,
  `value_text` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============ CORRECTIF : résultats « autonomes » (module Résultats : sans examen, avec date) ============
-- Le code (Grade::storeResult, GradeController) enregistre results.result_date et exam_id = NULL, absents du schéma d'origine.
ALTER TABLE `results` ADD COLUMN `result_date` DATE DEFAULT NULL AFTER `max_marks`;
ALTER TABLE `results` MODIFY `exam_id` INT UNSIGNED DEFAULT NULL;

SET FOREIGN_KEY_CHECKS = 1;


-- ===== database/seeds/demo_data.sql =====
-- Données de démonstration (NE PAS charger en production). Prérequis : migrations 0001-0003.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Catégories de dépenses par défaut (créées seulement si la table est vide).
INSERT INTO expense_categories (name, description) VALUES
('Fournitures scolaires', 'Papeterie, manuels et matériel didactique'),
('Loyer de l''école', 'Loyer des locaux'),
('Électricité (SNEL)', 'Factures d''électricité'),
('Eau (REGIDESO)', 'Factures d''eau'),
('Internet', 'Connexion internet'),
('Prime des enseignants', 'Primes et motivation du personnel enseignant'),
('Équipement de bureau', 'Machines et matériel de bureau'),
('Mobilier', 'Bancs, tables, chaises'),
('Entretien', 'Réparations et entretien des bâtiments'),
('Transport', 'Transport scolaire et carburant'),
('Autres', 'Dépenses diverses');

-- ---------------------------------------------------------------------
-- UTILISATEURS  (mot de passe des comptes de test : voir en-tête)
-- ---------------------------------------------------------------------
INSERT INTO `users` (`id`, `role_id`, `username`, `email`, `password`, `first_name`, `last_name`, `phone`, `status`, `last_login`) VALUES
(1, 1, 'admin', 'admin@school.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super', 'Administrateur', '+243 81 000 0100', 'active', NULL),
(3, 4, 'teacher1', 'teacher1@school.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pascal', 'Mukendi', '+243 81 000 0102', 'active', NULL),
(4, 5, 'student1', 'student1@school.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jonathan', 'Mbuyi', '+243 81 000 0103', 'active', NULL),
(5, 6, 'parent1', 'parent1@school.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Joseph', 'Mbuyi', '+243 81 000 0104', 'active', NULL),
(8, 2, 'admin_school', 'admin@school.com', '$2y$10$M81u98VHTxdq4tij9HqS2uvkLJ5Mzh1hkiW9gLHTd9smDsbM5z2Qq', 'Joël', 'Kasongo', '+243 81 000 0199', 'active', NULL),
(9, 4, 'teacher', 'teacher@school.com', '$2y$10$gQnm.0ZRU483CcpveUMbwOaKA3NDWm4u7hPEPi/1NUXl4kar5Noom', 'Grâce', 'Ilunga', '+243 81 000 0198', 'active', NULL),
(11, 3, 'prefet', 'prefet@school.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prosper', 'Mbayo', '+243 81 000 0201', 'active', NULL),
(12, 7, 'proviseur', 'proviseur@school.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Léon', 'Tshimanga', '+243 81 000 0202', 'active', NULL),
(13, 8, 'discipline', 'discipline@school.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rachel', 'Nkulu', '+243 81 000 0203', 'active', NULL),
(14, 9, 'cashier', 'cashier@school.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Béatrice', 'Mulumba', '+243 81 000 0204', 'active', NULL),
(15, 10, 'director', 'director@school.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Odette', 'Kabeya', '+243 81 000 0205', 'active', NULL),
(10, 5, 'student', 'student@school.com', '$2y$10$Tvregm21dkHPJ9OVjlVvA.qzqxtckesdz49KBmFz3gBxzffzXY.DO', 'Patience', 'Kalala', '+243 81 000 0197', 'active', NULL);

-- ---------------------------------------------------------------------
-- ANNÉES SCOLAIRES (rentrée début septembre, fin début juillet)
-- ---------------------------------------------------------------------
INSERT INTO `academic_sessions` (`id`, `name`, `start_date`, `end_date`, `is_current`) VALUES
(1, '2026 - 2027', '2026-09-07', '2027-07-02', 1),
(2, '2025 - 2026', '2025-09-01', '2026-07-03', 0);

-- ---------------------------------------------------------------------
-- CLASSES (cycle maternel, primaire, secondaire) ET DIVISIONS (A, B, ...)
-- Éducation de base = 6 ans de primaire + 2 ans de cycle terminal (7ème-8ème)
-- Humanités = 4 ans (1ère à 4ème) qui mènent à l'Examen d'État.
-- ---------------------------------------------------------------------
INSERT INTO `classes` (`id`, `name`, `numeric_name`, `cycle`, `class_teacher_id`) VALUES
(1, '1ère Maternelle', '1', 'maternel', 6),
(2, '2ème Maternelle', '2', 'maternel', NULL),
(3, '3ème Maternelle', '3', 'maternel', NULL),
(4, '1ère Primaire', '1', 'primaire', 4),
(5, '2ème Primaire', '2', 'primaire', 4),
(6, '3ème Primaire', '3', 'primaire', NULL),
(7, '4ème Primaire', '4', 'primaire', NULL),
(8, '5ème Primaire', '5', 'primaire', NULL),
(9, '6ème Primaire', '6', 'primaire', NULL),
(10, '7ème Éducation de base (CTEB)', '1', 'secondaire', NULL),
(11, '8ème Éducation de base (CTEB)', '2', 'secondaire', NULL),
(12, '1ère Humanités Biologie-Chimie', '3', 'secondaire', NULL),
(13, '2ème Humanités Biologie-Chimie', '4', 'secondaire', NULL),
(14, '3ème Humanités Biologie-Chimie', '5', 'secondaire', NULL),
(15, '4ème Humanités Biologie-Chimie', '6', 'secondaire', NULL),
(16, '1ère Humanités Mathématiques-Physique', '3', 'secondaire', NULL),
(17, '2ème Humanités Mathématiques-Physique', '4', 'secondaire', NULL),
(18, '3ème Humanités Mathématiques-Physique', '5', 'secondaire', NULL),
(19, '4ème Humanités Mathématiques-Physique', '6', 'secondaire', NULL),
(20, '1ère Humanités Pédagogie générale', '3', 'secondaire', NULL),
(21, '2ème Humanités Pédagogie générale', '4', 'secondaire', NULL),
(22, '3ème Humanités Pédagogie générale', '5', 'secondaire', NULL),
(23, '4ème Humanités Pédagogie générale', '6', 'secondaire', NULL),
(24, '1ère Humanités Commerciale et gestion', '3', 'secondaire', NULL),
(25, '2ème Humanités Commerciale et gestion', '4', 'secondaire', NULL),
(26, '3ème Humanités Commerciale et gestion', '5', 'secondaire', NULL),
(27, '4ème Humanités Commerciale et gestion', '6', 'secondaire', NULL),
(28, '1ère Humanités Littéraire', '3', 'secondaire', NULL),
(29, '2ème Humanités Littéraire', '4', 'secondaire', NULL),
(30, '3ème Humanités Littéraire', '5', 'secondaire', NULL),
(31, '4ème Humanités Littéraire', '6', 'secondaire', NULL),
(32, '1ère Humanités Électricité', '3', 'secondaire', NULL),
(33, '2ème Humanités Électricité', '4', 'secondaire', NULL),
(34, '3ème Humanités Électricité', '5', 'secondaire', NULL),
(35, '4ème Humanités Électricité', '6', 'secondaire', NULL);

INSERT INTO `sections` (`id`, `class_id`, `name`, `room_no`, `capacity`) VALUES
(1, 1, 'A', 'Salle 1', 30),
(2, 2, 'A', 'Salle 2', 30),
(3, 3, 'A', 'Salle 3', 30),
(4, 4, 'A', 'Salle 4', 50),
(5, 4, 'B', 'Salle 5', 50),
(6, 5, 'A', 'Salle 6', 50),
(7, 5, 'B', 'Salle 7', 50),
(8, 6, 'A', 'Salle 8', 50),
(9, 6, 'B', 'Salle 9', 50),
(10, 7, 'A', 'Salle 10', 50),
(11, 7, 'B', 'Salle 11', 50),
(12, 8, 'A', 'Salle 12', 50),
(13, 8, 'B', 'Salle 13', 50),
(14, 9, 'A', 'Salle 14', 50),
(15, 9, 'B', 'Salle 15', 50),
(16, 10, 'A', 'Salle 16', 50),
(17, 10, 'B', 'Salle 17', 50),
(18, 11, 'A', 'Salle 18', 50),
(19, 11, 'B', 'Salle 19', 50),
(20, 12, 'A', 'Salle 20', 45),
(21, 13, 'A', 'Salle 21', 45),
(22, 14, 'A', 'Salle 22', 45),
(23, 15, 'A', 'Salle 23', 45),
(24, 16, 'A', 'Salle 24', 45),
(25, 17, 'A', 'Salle 25', 45),
(26, 18, 'A', 'Salle 26', 45),
(27, 19, 'A', 'Salle 27', 45),
(28, 20, 'A', 'Salle 28', 45),
(29, 21, 'A', 'Salle 29', 45),
(30, 22, 'A', 'Salle 30', 45),
(31, 23, 'A', 'Salle 31', 45),
(32, 24, 'A', 'Salle 32', 45),
(33, 25, 'A', 'Salle 33', 45),
(34, 26, 'A', 'Salle 34', 45),
(35, 27, 'A', 'Salle 35', 45),
(36, 28, 'A', 'Salle 36', 45),
(37, 29, 'A', 'Salle 37', 45),
(38, 30, 'A', 'Salle 38', 45),
(39, 31, 'A', 'Salle 39', 45),
(40, 32, 'A', 'Salle 40', 45),
(41, 33, 'A', 'Salle 41', 45),
(42, 34, 'A', 'Salle 42', 45),
(43, 35, 'A', 'Salle 43', 45);

-- ---------------------------------------------------------------------
-- ENSEIGNANTS  (employee_id = matricule ; teaching_level = maternel / primaire / secondaire)
-- ---------------------------------------------------------------------
INSERT INTO `teachers` (`id`, `user_id`, `employee_id`, `official_id`, `first_name`, `last_name`, `gender`, `department`, `teaching_level`, `qualification`, `specialization`, `experience_years`, `phone`, `email`, `salary`, `joining_date`, `status`) VALUES
(1, 3, 'MAT-0001', 'ENS-0001', 'Pascal', 'Mukendi', 'male', 'Mathématiques', 'secondaire', 'Licence en Mathématiques (UPN)', 'Algèbre', 8, '+243 81 000 0102', 'teacher1@school.test', 250.0, '2019-09-02', 'active'),
(2, NULL, 'MAT-0002', 'ENS-0002', 'Dieudonné', 'Kabongo', 'male', 'Sciences', 'secondaire', 'Licence en Physique (UNIKIN)', 'Physique', 6, '+243 81 000 0110', 'dieudonne.kabongo@school.test', 260.0, '2020-09-01', 'active'),
(3, NULL, 'MAT-0003', 'ENS-0003', 'Espérance', 'Mbuyi', 'female', 'Français', 'secondaire', 'Licence en Lettres et Civilisation françaises', 'Littérature française', 10, '+243 81 000 0111', 'esperance.mbuyi@school.test', 270.0, '2016-09-05', 'active'),
(4, 9, 'MAT-0004', 'ENS-0004', 'Grâce', 'Ilunga', 'female', 'Enseignement primaire', 'primaire', 'D6 Pédagogie générale', 'Instituteur primaire', 5, '+243 81 000 0198', 'teacher@school.com', 220.0, '2021-09-06', 'active'),
(5, NULL, 'MAT-0005', 'ENS-0005', 'Jean-Marie', 'Ngoy', 'male', 'Histoire-Géographie', 'secondaire', 'Gradué en Histoire (ISP)', 'Histoire', 7, '+243 81 000 0112', 'jeanmarie.ngoy@school.test', 230.0, '2018-09-03', 'active'),
(6, NULL, 'MAT-0006', 'ENS-0006', 'Prisca', 'Lumbala', 'female', 'Maternelle', 'maternel', 'D6 Pédagogie (option maternelle)', 'Éducation maternelle', 4, '+243 81 000 0113', 'prisca.lumbala@school.test', 200.0, '2022-09-05', 'active');

-- ---------------------------------------------------------------------
-- ÉLÈVES   first_name = Nom | middle_name = Postnom | last_name = Prénom
-- ---------------------------------------------------------------------
INSERT INTO `students` (`id`, `user_id`, `official_id`, `admission_no`, `roll_no`, `first_name`, `middle_name`, `last_name`, `gender`, `dob`, `birth_place`, `nationality`, `perm_number`, `class_id`, `section_id`, `guardian_name`, `guardian_relation`, `guardian_phone`, `guardian_email`, `address`, `city`, `state`, `phone`, `email`, `blood_group`, `medical_info`, `status`, `admission_date`) VALUES
(1, 4, 'ELV-0001', 'ADM-2026-0001', '01', 'Mbuyi', 'Tshibola', 'Jonathan', 'male', '2019-05-12', 'Kinshasa', 'Congolaise', 'PERM-100001', 4, 4, 'Joseph Mbuyi', 'Père', '+243 81 000 0104', 'parent1@school.test', '12, Av. de la Libération, Q/ Mbinza, C/ Ngaliema', 'Kinshasa', 'Kinshasa', '+243 81 000 0103', 'student1@school.test', 'O+', 'Aucune', 'active', '2026-09-07'),
(2, NULL, 'ELV-0002', 'ADM-2026-0002', '02', 'Kabongo', 'Mwamba', 'Esther', 'female', '2019-08-03', 'Kinshasa', 'Congolaise', 'PERM-100002', 4, 4, 'Sarah Mwamba', 'Mère', '+243 81 000 0112', 'sarah.mwamba@school.test', '45, Av. Kasa-Vubu, Q/ Kalamu, C/ Kalamu', 'Kinshasa', 'Kinshasa', NULL, NULL, 'A+', 'Asthme léger', 'active', '2026-09-07'),
(3, NULL, 'ELV-0003', 'ADM-2026-0003', '03', 'Ilunga', 'Banza', 'Samuel', 'male', '2019-02-21', 'Lubumbashi', 'Congolaise', 'PERM-100003', 4, 5, 'Michel Ilunga', 'Père', '+243 81 000 0114', 'michel.ilunga@school.test', '78, Av. Lumumba, Q/ Makomeno, C/ Limete', 'Kinshasa', 'Kinshasa', NULL, NULL, 'B+', 'Aucune', 'active', '2026-09-07'),
(4, NULL, 'ELV-0004', 'ADM-2026-0004', '04', 'Lukusa', 'Kanku', 'Divine', 'female', '2017-07-15', 'Mbuji-Mayi', 'Congolaise', 'PERM-100004', 6, 8, 'Jacques Lukusa', 'Père', '+243 81 000 0116', 'jacques.lukusa@school.test', '9, Av. des Poids Lourds, Q/ Righini, C/ Lemba', 'Kinshasa', 'Kinshasa', NULL, NULL, 'AB', 'Aucune', 'active', '2025-09-01'),
(5, NULL, 'ELV-0005', 'ADM-2025-0100', '10', 'Nzuzi', 'Makiese', 'Christian', 'male', '2012-03-30', 'Matadi', 'Congolaise', 'PERM-100005', 11, 18, 'Laure Nzuzi', 'Mère', '+243 81 000 0118', 'laure.nzuzi@school.test', '22, Av. Kimwenza, Q/ Mont-Fleury, C/ Mont-Ngafula', 'Kinshasa', 'Kinshasa', NULL, NULL, 'O-', 'Aucune', 'active', '2024-09-02'),
(6, NULL, 'ELV-0006', 'ADM-2026-0006', '01', 'Mavungu', 'Kitoko', 'Ruth', 'female', '2018-09-14', 'Kinshasa', 'Congolaise', 'PERM-100006', 5, 6, 'Youssouf Mavungu', 'Père', '+243 81 000 0122', 'youssouf.mavungu@school.test', '5, Av. Kabinda, Q/ Bandal, C/ Bandalungwa', 'Kinshasa', 'Kinshasa', NULL, NULL, 'B', 'Aucune', 'active', '2026-09-07'),
(7, 10, 'ELV-0007', 'ADM-2026-0007', '02', 'Kalala', 'Mputu', 'Patience', 'female', '2018-06-20', 'Kinshasa', 'Congolaise', 'PERM-100007', 5, 6, 'Hassan Kalala', 'Père', '+243 81 000 0197', 'student@school.com', '10, Av. de l''École, Q/ Matonge, C/ Kalamu', 'Kinshasa', 'Kinshasa', '+243 81 000 0197', 'student@school.com', 'O+', 'Aucune', 'active', '2026-09-07'),
(8, NULL, 'ELV-0008', 'ADM-2026-0008', '05', 'Bolingo', 'Lokua', 'Héritier', 'male', '2022-04-10', 'Kinshasa', 'Congolaise', 'PERM-100008', 1, 1, 'Nadine Lokua', 'Mère', '+243 81 000 0125', 'nadine.lokua@school.test', '3, Av. Tombalbaye, Q/ Gombe, C/ Gombe', 'Kinshasa', 'Kinshasa', NULL, NULL, 'A+', 'Aucune', 'active', '2026-09-07'),
(9, NULL, 'ELV-0009', 'ADM-2023-0042', '07', 'Mpoyi', 'Kasanda', 'Merveille', 'female', '2009-11-02', 'Kananga', 'Congolaise', 'PERM-100009', 18, 26, 'Félicien Mpoyi', 'Père', '+243 81 000 0127', 'felicien.mpoyi@school.test', '31, Av. Mondjiba, Q/ Binza Delvaux, C/ Ngaliema', 'Kinshasa', 'Kinshasa', NULL, NULL, 'AB', 'Aucune', 'active', '2023-09-04');

-- ---------------------------------------------------------------------
-- PARENTS
-- ---------------------------------------------------------------------
INSERT INTO `parents` (`id`, `user_id`, `official_id`, `father_name`, `mother_name`, `guardian_name`, `occupation`, `phone`, `email`, `address`) VALUES
(1, 5, 'PAR-0001', 'Joseph Mbuyi', 'Berthe Tshibola', 'Joseph Mbuyi', 'Ingénieur civil', '+243 81 000 0104', 'parent1@school.test', '12, Av. de la Libération, Q/ Mbinza, C/ Ngaliema, Kinshasa');

INSERT INTO `student_parents` (`student_id`, `parent_id`) VALUES (1, 1);

INSERT INTO `official_id_counters` (`prefix`, `last_number`) VALUES ('ELV', 9), ('ENS', 6), ('PAR', 1);

-- ---------------------------------------------------------------------
-- COURS PAR CLASSE  (credits = heures par semaine)
-- Programme simplifié : à ajuster selon le programme national officiel
-- ---------------------------------------------------------------------
INSERT INTO `subjects` (`id`, `name`, `code`, `class_id`, `teacher_id`, `credits`, `description`) VALUES
(1, 'Éveil au langage (Français)', 'MAT1-01', 1, 6, 3, NULL),
(2, 'Éveil mathématique', 'MAT1-02', 1, 6, 3, NULL),
(3, 'Éveil scientifique et social', 'MAT1-03', 1, 6, 3, NULL),
(4, 'Activités artistiques (dessin, chant)', 'MAT1-04', 1, 6, 3, NULL),
(5, 'Psychomotricité et jeux', 'MAT1-05', 1, 6, 3, NULL),
(6, 'Éducation religieuse et morale', 'MAT1-06', 1, 6, 3, NULL),
(7, 'Éveil au langage (Français)', 'MAT2-01', 2, 6, 3, NULL),
(8, 'Éveil mathématique', 'MAT2-02', 2, 6, 3, NULL),
(9, 'Éveil scientifique et social', 'MAT2-03', 2, 6, 3, NULL),
(10, 'Activités artistiques (dessin, chant)', 'MAT2-04', 2, 6, 3, NULL),
(11, 'Psychomotricité et jeux', 'MAT2-05', 2, 6, 3, NULL),
(12, 'Éducation religieuse et morale', 'MAT2-06', 2, 6, 3, NULL),
(13, 'Éveil au langage (Français)', 'MAT3-01', 3, 6, 3, NULL),
(14, 'Éveil mathématique', 'MAT3-02', 3, 6, 3, NULL),
(15, 'Éveil scientifique et social', 'MAT3-03', 3, 6, 3, NULL),
(16, 'Activités artistiques (dessin, chant)', 'MAT3-04', 3, 6, 3, NULL),
(17, 'Psychomotricité et jeux', 'MAT3-05', 3, 6, 3, NULL),
(18, 'Éducation religieuse et morale', 'MAT3-06', 3, 6, 3, NULL),
(19, 'Français', 'FRA-P1', 4, 4, 8, NULL),
(20, 'Mathématiques', 'MAT-P1', 4, 4, 7, NULL),
(21, 'Sciences d''éveil', 'SCI-P1', 4, 4, 3, NULL),
(22, 'Éducation civique et morale', 'ECM-P1', 4, 4, 2, NULL),
(23, 'Éducation à la vie courante', 'EVC-P1', 4, 4, 2, NULL),
(24, 'Éducation physique', 'EPH-P1', 4, 4, 2, NULL),
(25, 'Dessin et arts plastiques', 'DES-P1', 4, 4, 1, NULL),
(26, 'Chant et musique', 'CHA-P1', 4, 4, 1, NULL),
(27, 'Éducation religieuse', 'REL-P1', 4, 4, 2, NULL),
(28, 'Langue nationale (lingala, swahili, kikongo, tshiluba)', 'LNA-P1', 4, 4, 2, NULL),
(29, 'Français', 'FRA-P2', 5, 4, 8, NULL),
(30, 'Mathématiques', 'MAT-P2', 5, 4, 7, NULL),
(31, 'Sciences d''éveil', 'SCI-P2', 5, 4, 3, NULL),
(32, 'Éducation civique et morale', 'ECM-P2', 5, 4, 2, NULL),
(33, 'Éducation à la vie courante', 'EVC-P2', 5, 4, 2, NULL),
(34, 'Éducation physique', 'EPH-P2', 5, 4, 2, NULL),
(35, 'Dessin et arts plastiques', 'DES-P2', 5, 4, 1, NULL),
(36, 'Chant et musique', 'CHA-P2', 5, 4, 1, NULL),
(37, 'Éducation religieuse', 'REL-P2', 5, 4, 2, NULL),
(38, 'Langue nationale (lingala, swahili, kikongo, tshiluba)', 'LNA-P2', 5, 4, 2, NULL),
(39, 'Français', 'FRA-P3', 6, 3, 8, NULL),
(40, 'Mathématiques', 'MAT-P3', 6, 1, 7, NULL),
(41, 'Sciences d''éveil', 'SCI-P3', 6, 2, 3, NULL),
(42, 'Éducation civique et morale', 'ECM-P3', 6, 4, 2, NULL),
(43, 'Éducation à la vie courante', 'EVC-P3', 6, 4, 2, NULL),
(44, 'Éducation physique', 'EPH-P3', 6, 4, 2, NULL),
(45, 'Dessin et arts plastiques', 'DES-P3', 6, 4, 1, NULL),
(46, 'Chant et musique', 'CHA-P3', 6, 4, 1, NULL),
(47, 'Éducation religieuse', 'REL-P3', 6, 4, 2, NULL),
(48, 'Langue nationale (lingala, swahili, kikongo, tshiluba)', 'LNA-P3', 6, 4, 2, NULL),
(49, 'Anglais', 'ANG-P3', 6, 3, 2, NULL),
(50, 'Français', 'FRA-P4', 7, 3, 8, NULL),
(51, 'Mathématiques', 'MAT-P4', 7, 1, 7, NULL),
(52, 'Sciences d''éveil', 'SCI-P4', 7, 2, 3, NULL),
(53, 'Éducation civique et morale', 'ECM-P4', 7, 4, 2, NULL),
(54, 'Éducation à la vie courante', 'EVC-P4', 7, 4, 2, NULL),
(55, 'Éducation physique', 'EPH-P4', 7, 4, 2, NULL),
(56, 'Dessin et arts plastiques', 'DES-P4', 7, 4, 1, NULL),
(57, 'Chant et musique', 'CHA-P4', 7, 4, 1, NULL),
(58, 'Éducation religieuse', 'REL-P4', 7, 4, 2, NULL),
(59, 'Langue nationale (lingala, swahili, kikongo, tshiluba)', 'LNA-P4', 7, 4, 2, NULL),
(60, 'Anglais', 'ANG-P4', 7, 3, 2, NULL),
(61, 'Histoire', 'HIS-P4', 7, 5, 2, NULL),
(62, 'Géographie', 'GEO-P4', 7, 5, 2, NULL),
(63, 'Français', 'FRA-P5', 8, 3, 8, NULL),
(64, 'Mathématiques', 'MAT-P5', 8, 1, 7, NULL),
(65, 'Sciences d''éveil', 'SCI-P5', 8, 2, 3, NULL),
(66, 'Éducation civique et morale', 'ECM-P5', 8, 4, 2, NULL),
(67, 'Éducation à la vie courante', 'EVC-P5', 8, 4, 2, NULL),
(68, 'Éducation physique', 'EPH-P5', 8, 4, 2, NULL),
(69, 'Dessin et arts plastiques', 'DES-P5', 8, 4, 1, NULL),
(70, 'Chant et musique', 'CHA-P5', 8, 4, 1, NULL),
(71, 'Éducation religieuse', 'REL-P5', 8, 4, 2, NULL),
(72, 'Langue nationale (lingala, swahili, kikongo, tshiluba)', 'LNA-P5', 8, 4, 2, NULL),
(73, 'Anglais', 'ANG-P5', 8, 3, 2, NULL),
(74, 'Histoire', 'HIS-P5', 8, 5, 2, NULL),
(75, 'Géographie', 'GEO-P5', 8, 5, 2, NULL),
(76, 'Français', 'FRA-P6', 9, 3, 8, NULL),
(77, 'Mathématiques', 'MAT-P6', 9, 1, 7, NULL),
(78, 'Sciences d''éveil', 'SCI-P6', 9, 2, 3, NULL),
(79, 'Éducation civique et morale', 'ECM-P6', 9, 4, 2, NULL),
(80, 'Éducation à la vie courante', 'EVC-P6', 9, 4, 2, NULL),
(81, 'Éducation physique', 'EPH-P6', 9, 4, 2, NULL),
(82, 'Dessin et arts plastiques', 'DES-P6', 9, 4, 1, NULL),
(83, 'Chant et musique', 'CHA-P6', 9, 4, 1, NULL),
(84, 'Éducation religieuse', 'REL-P6', 9, 4, 2, NULL),
(85, 'Langue nationale (lingala, swahili, kikongo, tshiluba)', 'LNA-P6', 9, 4, 2, NULL),
(86, 'Anglais', 'ANG-P6', 9, 3, 2, NULL),
(87, 'Histoire', 'HIS-P6', 9, 5, 2, NULL),
(88, 'Géographie', 'GEO-P6', 9, 5, 2, NULL),
(89, 'Français', 'FRA-EB7', 10, 3, 5, NULL),
(90, 'Mathématiques', 'MAT-EB7', 10, 1, 5, NULL),
(91, 'Anglais', 'ANG-EB7', 10, 3, 3, NULL),
(92, 'Sciences (biologie, physique, chimie)', 'SCI-EB7', 10, 2, 4, NULL),
(93, 'Histoire', 'HIS-EB7', 10, 5, 2, NULL),
(94, 'Géographie', 'GEO-EB7', 10, 5, 2, NULL),
(95, 'Éducation civique et morale', 'ECM-EB7', 10, 5, 1, NULL),
(96, 'Éducation physique', 'EPH-EB7', 10, NULL, 2, NULL),
(97, 'Informatique', 'INF-EB7', 10, NULL, 2, NULL),
(98, 'Éducation à la vie courante', 'EVC-EB7', 10, NULL, 2, NULL),
(99, 'Éducation religieuse', 'REL-EB7', 10, NULL, 1, NULL),
(100, 'Langue nationale', 'LNA-EB7', 10, NULL, 2, NULL),
(101, 'Français', 'FRA-EB8', 11, 3, 5, NULL),
(102, 'Mathématiques', 'MAT-EB8', 11, 1, 5, NULL),
(103, 'Anglais', 'ANG-EB8', 11, 3, 3, NULL),
(104, 'Sciences (biologie, physique, chimie)', 'SCI-EB8', 11, 2, 4, NULL),
(105, 'Histoire', 'HIS-EB8', 11, 5, 2, NULL),
(106, 'Géographie', 'GEO-EB8', 11, 5, 2, NULL),
(107, 'Éducation civique et morale', 'ECM-EB8', 11, 5, 1, NULL),
(108, 'Éducation physique', 'EPH-EB8', 11, NULL, 2, NULL),
(109, 'Informatique', 'INF-EB8', 11, NULL, 2, NULL),
(110, 'Éducation à la vie courante', 'EVC-EB8', 11, NULL, 2, NULL),
(111, 'Éducation religieuse', 'REL-EB8', 11, NULL, 1, NULL),
(112, 'Langue nationale', 'LNA-EB8', 11, NULL, 2, NULL),
(113, 'Français', 'FRA-H1BC', 12, 3, 4, NULL),
(114, 'Anglais', 'ANG-H1BC', 12, 3, 3, NULL),
(115, 'Éducation civique et morale', 'ECM-H1BC', 12, 5, 1, NULL),
(116, 'Éducation physique', 'EPH-H1BC', 12, NULL, 2, NULL),
(117, 'Informatique', 'INF-H1BC', 12, NULL, 2, NULL),
(118, 'Éducation religieuse', 'REL-H1BC', 12, NULL, 1, NULL),
(119, 'Biologie', 'BIO-H1BC', 12, 2, 5, NULL),
(120, 'Chimie', 'CHI-H1BC', 12, 2, 4, NULL),
(121, 'Mathématiques', 'MAT-H1BC', 12, 1, 3, NULL),
(122, 'Physique', 'PHY-H1BC', 12, 2, 3, NULL),
(123, 'Géographie', 'GEO-H1BC', 12, 5, 2, NULL),
(124, 'Français', 'FRA-H2BC', 13, 3, 4, NULL),
(125, 'Anglais', 'ANG-H2BC', 13, 3, 3, NULL),
(126, 'Éducation civique et morale', 'ECM-H2BC', 13, 5, 1, NULL),
(127, 'Éducation physique', 'EPH-H2BC', 13, NULL, 2, NULL),
(128, 'Informatique', 'INF-H2BC', 13, NULL, 2, NULL),
(129, 'Éducation religieuse', 'REL-H2BC', 13, NULL, 1, NULL),
(130, 'Biologie', 'BIO-H2BC', 13, 2, 5, NULL),
(131, 'Chimie', 'CHI-H2BC', 13, 2, 4, NULL),
(132, 'Mathématiques', 'MAT-H2BC', 13, 1, 3, NULL),
(133, 'Physique', 'PHY-H2BC', 13, 2, 3, NULL),
(134, 'Géographie', 'GEO-H2BC', 13, 5, 2, NULL),
(135, 'Français', 'FRA-H3BC', 14, 3, 4, NULL),
(136, 'Anglais', 'ANG-H3BC', 14, 3, 3, NULL),
(137, 'Éducation civique et morale', 'ECM-H3BC', 14, 5, 1, NULL),
(138, 'Éducation physique', 'EPH-H3BC', 14, NULL, 2, NULL),
(139, 'Informatique', 'INF-H3BC', 14, NULL, 2, NULL),
(140, 'Éducation religieuse', 'REL-H3BC', 14, NULL, 1, NULL),
(141, 'Biologie', 'BIO-H3BC', 14, 2, 5, NULL),
(142, 'Chimie', 'CHI-H3BC', 14, 2, 4, NULL),
(143, 'Mathématiques', 'MAT-H3BC', 14, 1, 3, NULL),
(144, 'Physique', 'PHY-H3BC', 14, 2, 3, NULL),
(145, 'Géographie', 'GEO-H3BC', 14, 5, 2, NULL),
(146, 'Français', 'FRA-H4BC', 15, 3, 4, NULL),
(147, 'Anglais', 'ANG-H4BC', 15, 3, 3, NULL),
(148, 'Éducation civique et morale', 'ECM-H4BC', 15, 5, 1, NULL),
(149, 'Éducation physique', 'EPH-H4BC', 15, NULL, 2, NULL),
(150, 'Informatique', 'INF-H4BC', 15, NULL, 2, NULL),
(151, 'Éducation religieuse', 'REL-H4BC', 15, NULL, 1, NULL),
(152, 'Biologie', 'BIO-H4BC', 15, 2, 5, NULL),
(153, 'Chimie', 'CHI-H4BC', 15, 2, 4, NULL),
(154, 'Mathématiques', 'MAT-H4BC', 15, 1, 3, NULL),
(155, 'Physique', 'PHY-H4BC', 15, 2, 3, NULL),
(156, 'Géographie', 'GEO-H4BC', 15, 5, 2, NULL),
(157, 'Français', 'FRA-H1MP', 16, 3, 4, NULL),
(158, 'Anglais', 'ANG-H1MP', 16, 3, 3, NULL),
(159, 'Éducation civique et morale', 'ECM-H1MP', 16, 5, 1, NULL),
(160, 'Éducation physique', 'EPH-H1MP', 16, NULL, 2, NULL),
(161, 'Informatique', 'INF-H1MP', 16, NULL, 2, NULL),
(162, 'Éducation religieuse', 'REL-H1MP', 16, NULL, 1, NULL),
(163, 'Mathématiques', 'MAT-H1MP', 16, 1, 7, NULL),
(164, 'Physique', 'PHY-H1MP', 16, 2, 5, NULL),
(165, 'Chimie', 'CHI-H1MP', 16, 2, 3, NULL),
(166, 'Dessin technique', 'DTE-H1MP', 16, NULL, 2, NULL),
(167, 'Biologie', 'BIO-H1MP', 16, 2, 2, NULL),
(168, 'Français', 'FRA-H2MP', 17, 3, 4, NULL),
(169, 'Anglais', 'ANG-H2MP', 17, 3, 3, NULL),
(170, 'Éducation civique et morale', 'ECM-H2MP', 17, 5, 1, NULL),
(171, 'Éducation physique', 'EPH-H2MP', 17, NULL, 2, NULL),
(172, 'Informatique', 'INF-H2MP', 17, NULL, 2, NULL),
(173, 'Éducation religieuse', 'REL-H2MP', 17, NULL, 1, NULL),
(174, 'Mathématiques', 'MAT-H2MP', 17, 1, 7, NULL),
(175, 'Physique', 'PHY-H2MP', 17, 2, 5, NULL),
(176, 'Chimie', 'CHI-H2MP', 17, 2, 3, NULL),
(177, 'Dessin technique', 'DTE-H2MP', 17, NULL, 2, NULL),
(178, 'Biologie', 'BIO-H2MP', 17, 2, 2, NULL),
(179, 'Français', 'FRA-H3MP', 18, 3, 4, NULL),
(180, 'Anglais', 'ANG-H3MP', 18, 3, 3, NULL),
(181, 'Éducation civique et morale', 'ECM-H3MP', 18, 5, 1, NULL),
(182, 'Éducation physique', 'EPH-H3MP', 18, NULL, 2, NULL),
(183, 'Informatique', 'INF-H3MP', 18, NULL, 2, NULL),
(184, 'Éducation religieuse', 'REL-H3MP', 18, NULL, 1, NULL),
(185, 'Mathématiques', 'MAT-H3MP', 18, 1, 7, NULL),
(186, 'Physique', 'PHY-H3MP', 18, 2, 5, NULL),
(187, 'Chimie', 'CHI-H3MP', 18, 2, 3, NULL),
(188, 'Dessin technique', 'DTE-H3MP', 18, NULL, 2, NULL),
(189, 'Biologie', 'BIO-H3MP', 18, 2, 2, NULL),
(190, 'Français', 'FRA-H4MP', 19, 3, 4, NULL),
(191, 'Anglais', 'ANG-H4MP', 19, 3, 3, NULL),
(192, 'Éducation civique et morale', 'ECM-H4MP', 19, 5, 1, NULL),
(193, 'Éducation physique', 'EPH-H4MP', 19, NULL, 2, NULL),
(194, 'Informatique', 'INF-H4MP', 19, NULL, 2, NULL),
(195, 'Éducation religieuse', 'REL-H4MP', 19, NULL, 1, NULL),
(196, 'Mathématiques', 'MAT-H4MP', 19, 1, 7, NULL),
(197, 'Physique', 'PHY-H4MP', 19, 2, 5, NULL),
(198, 'Chimie', 'CHI-H4MP', 19, 2, 3, NULL),
(199, 'Dessin technique', 'DTE-H4MP', 19, NULL, 2, NULL),
(200, 'Biologie', 'BIO-H4MP', 19, 2, 2, NULL),
(201, 'Français', 'FRA-H1PG', 20, 3, 4, NULL),
(202, 'Anglais', 'ANG-H1PG', 20, 3, 3, NULL),
(203, 'Éducation civique et morale', 'ECM-H1PG', 20, 5, 1, NULL),
(204, 'Éducation physique', 'EPH-H1PG', 20, NULL, 2, NULL),
(205, 'Informatique', 'INF-H1PG', 20, NULL, 2, NULL),
(206, 'Éducation religieuse', 'REL-H1PG', 20, NULL, 1, NULL),
(207, 'Pédagogie générale', 'PED-H1PG', 20, NULL, 5, NULL),
(208, 'Psychologie de l''enfant', 'PSY-H1PG', 20, NULL, 3, NULL),
(209, 'Didactique', 'DID-H1PG', 20, NULL, 4, NULL),
(210, 'Sociologie', 'SOC-H1PG', 20, NULL, 2, NULL),
(211, 'Mathématiques', 'MAT-H1PG', 20, 1, 3, NULL),
(212, 'Français', 'FRA-H2PG', 21, 3, 4, NULL),
(213, 'Anglais', 'ANG-H2PG', 21, 3, 3, NULL),
(214, 'Éducation civique et morale', 'ECM-H2PG', 21, 5, 1, NULL),
(215, 'Éducation physique', 'EPH-H2PG', 21, NULL, 2, NULL),
(216, 'Informatique', 'INF-H2PG', 21, NULL, 2, NULL),
(217, 'Éducation religieuse', 'REL-H2PG', 21, NULL, 1, NULL),
(218, 'Pédagogie générale', 'PED-H2PG', 21, NULL, 5, NULL),
(219, 'Psychologie de l''enfant', 'PSY-H2PG', 21, NULL, 3, NULL),
(220, 'Didactique', 'DID-H2PG', 21, NULL, 4, NULL),
(221, 'Sociologie', 'SOC-H2PG', 21, NULL, 2, NULL),
(222, 'Mathématiques', 'MAT-H2PG', 21, 1, 3, NULL),
(223, 'Français', 'FRA-H3PG', 22, 3, 4, NULL),
(224, 'Anglais', 'ANG-H3PG', 22, 3, 3, NULL),
(225, 'Éducation civique et morale', 'ECM-H3PG', 22, 5, 1, NULL),
(226, 'Éducation physique', 'EPH-H3PG', 22, NULL, 2, NULL),
(227, 'Informatique', 'INF-H3PG', 22, NULL, 2, NULL),
(228, 'Éducation religieuse', 'REL-H3PG', 22, NULL, 1, NULL),
(229, 'Pédagogie générale', 'PED-H3PG', 22, NULL, 5, NULL),
(230, 'Psychologie de l''enfant', 'PSY-H3PG', 22, NULL, 3, NULL),
(231, 'Didactique', 'DID-H3PG', 22, NULL, 4, NULL),
(232, 'Sociologie', 'SOC-H3PG', 22, NULL, 2, NULL),
(233, 'Mathématiques', 'MAT-H3PG', 22, 1, 3, NULL),
(234, 'Français', 'FRA-H4PG', 23, 3, 4, NULL),
(235, 'Anglais', 'ANG-H4PG', 23, 3, 3, NULL),
(236, 'Éducation civique et morale', 'ECM-H4PG', 23, 5, 1, NULL),
(237, 'Éducation physique', 'EPH-H4PG', 23, NULL, 2, NULL),
(238, 'Informatique', 'INF-H4PG', 23, NULL, 2, NULL),
(239, 'Éducation religieuse', 'REL-H4PG', 23, NULL, 1, NULL),
(240, 'Pédagogie générale', 'PED-H4PG', 23, NULL, 5, NULL),
(241, 'Psychologie de l''enfant', 'PSY-H4PG', 23, NULL, 3, NULL),
(242, 'Didactique', 'DID-H4PG', 23, NULL, 4, NULL),
(243, 'Sociologie', 'SOC-H4PG', 23, NULL, 2, NULL),
(244, 'Mathématiques', 'MAT-H4PG', 23, 1, 3, NULL),
(245, 'Français', 'FRA-H1CG', 24, 3, 4, NULL),
(246, 'Anglais', 'ANG-H1CG', 24, 3, 3, NULL),
(247, 'Éducation civique et morale', 'ECM-H1CG', 24, 5, 1, NULL),
(248, 'Éducation physique', 'EPH-H1CG', 24, NULL, 2, NULL),
(249, 'Informatique', 'INF-H1CG', 24, NULL, 2, NULL),
(250, 'Éducation religieuse', 'REL-H1CG', 24, NULL, 1, NULL),
(251, 'Comptabilité générale', 'CPT-H1CG', 24, NULL, 5, NULL),
(252, 'Droit commercial', 'DRC-H1CG', 24, NULL, 3, NULL),
(253, 'Économie politique', 'ECO-H1CG', 24, NULL, 3, NULL),
(254, 'Mathématiques financières', 'MFI-H1CG', 24, 1, 3, NULL),
(255, 'Bureautique et dactylographie', 'BUR-H1CG', 24, NULL, 2, NULL),
(256, 'Français', 'FRA-H2CG', 25, 3, 4, NULL),
(257, 'Anglais', 'ANG-H2CG', 25, 3, 3, NULL),
(258, 'Éducation civique et morale', 'ECM-H2CG', 25, 5, 1, NULL),
(259, 'Éducation physique', 'EPH-H2CG', 25, NULL, 2, NULL),
(260, 'Informatique', 'INF-H2CG', 25, NULL, 2, NULL),
(261, 'Éducation religieuse', 'REL-H2CG', 25, NULL, 1, NULL),
(262, 'Comptabilité générale', 'CPT-H2CG', 25, NULL, 5, NULL),
(263, 'Droit commercial', 'DRC-H2CG', 25, NULL, 3, NULL),
(264, 'Économie politique', 'ECO-H2CG', 25, NULL, 3, NULL),
(265, 'Mathématiques financières', 'MFI-H2CG', 25, 1, 3, NULL),
(266, 'Bureautique et dactylographie', 'BUR-H2CG', 25, NULL, 2, NULL),
(267, 'Français', 'FRA-H3CG', 26, 3, 4, NULL),
(268, 'Anglais', 'ANG-H3CG', 26, 3, 3, NULL),
(269, 'Éducation civique et morale', 'ECM-H3CG', 26, 5, 1, NULL),
(270, 'Éducation physique', 'EPH-H3CG', 26, NULL, 2, NULL),
(271, 'Informatique', 'INF-H3CG', 26, NULL, 2, NULL),
(272, 'Éducation religieuse', 'REL-H3CG', 26, NULL, 1, NULL),
(273, 'Comptabilité générale', 'CPT-H3CG', 26, NULL, 5, NULL),
(274, 'Droit commercial', 'DRC-H3CG', 26, NULL, 3, NULL),
(275, 'Économie politique', 'ECO-H3CG', 26, NULL, 3, NULL),
(276, 'Mathématiques financières', 'MFI-H3CG', 26, 1, 3, NULL),
(277, 'Bureautique et dactylographie', 'BUR-H3CG', 26, NULL, 2, NULL),
(278, 'Français', 'FRA-H4CG', 27, 3, 4, NULL),
(279, 'Anglais', 'ANG-H4CG', 27, 3, 3, NULL),
(280, 'Éducation civique et morale', 'ECM-H4CG', 27, 5, 1, NULL),
(281, 'Éducation physique', 'EPH-H4CG', 27, NULL, 2, NULL),
(282, 'Informatique', 'INF-H4CG', 27, NULL, 2, NULL),
(283, 'Éducation religieuse', 'REL-H4CG', 27, NULL, 1, NULL),
(284, 'Comptabilité générale', 'CPT-H4CG', 27, NULL, 5, NULL),
(285, 'Droit commercial', 'DRC-H4CG', 27, NULL, 3, NULL),
(286, 'Économie politique', 'ECO-H4CG', 27, NULL, 3, NULL),
(287, 'Mathématiques financières', 'MFI-H4CG', 27, 1, 3, NULL),
(288, 'Bureautique et dactylographie', 'BUR-H4CG', 27, NULL, 2, NULL),
(289, 'Français', 'FRA-H1LI', 28, 3, 4, NULL),
(290, 'Anglais', 'ANG-H1LI', 28, 3, 3, NULL),
(291, 'Éducation civique et morale', 'ECM-H1LI', 28, 5, 1, NULL),
(292, 'Éducation physique', 'EPH-H1LI', 28, NULL, 2, NULL),
(293, 'Informatique', 'INF-H1LI', 28, NULL, 2, NULL),
(294, 'Éducation religieuse', 'REL-H1LI', 28, NULL, 1, NULL),
(295, 'Latin', 'LAT-H1LI', 28, 3, 4, NULL),
(296, 'Philosophie', 'PHI-H1LI', 28, NULL, 3, NULL),
(297, 'Littérature française', 'LIT-H1LI', 28, 3, 4, NULL),
(298, 'Histoire', 'HIS-H1LI', 28, 5, 2, NULL),
(299, 'Géographie', 'GEO-H1LI', 28, 5, 2, NULL),
(300, 'Français', 'FRA-H2LI', 29, 3, 4, NULL),
(301, 'Anglais', 'ANG-H2LI', 29, 3, 3, NULL),
(302, 'Éducation civique et morale', 'ECM-H2LI', 29, 5, 1, NULL),
(303, 'Éducation physique', 'EPH-H2LI', 29, NULL, 2, NULL),
(304, 'Informatique', 'INF-H2LI', 29, NULL, 2, NULL),
(305, 'Éducation religieuse', 'REL-H2LI', 29, NULL, 1, NULL),
(306, 'Latin', 'LAT-H2LI', 29, 3, 4, NULL),
(307, 'Philosophie', 'PHI-H2LI', 29, NULL, 3, NULL),
(308, 'Littérature française', 'LIT-H2LI', 29, 3, 4, NULL),
(309, 'Histoire', 'HIS-H2LI', 29, 5, 2, NULL),
(310, 'Géographie', 'GEO-H2LI', 29, 5, 2, NULL),
(311, 'Français', 'FRA-H3LI', 30, 3, 4, NULL),
(312, 'Anglais', 'ANG-H3LI', 30, 3, 3, NULL),
(313, 'Éducation civique et morale', 'ECM-H3LI', 30, 5, 1, NULL),
(314, 'Éducation physique', 'EPH-H3LI', 30, NULL, 2, NULL),
(315, 'Informatique', 'INF-H3LI', 30, NULL, 2, NULL),
(316, 'Éducation religieuse', 'REL-H3LI', 30, NULL, 1, NULL),
(317, 'Latin', 'LAT-H3LI', 30, 3, 4, NULL),
(318, 'Philosophie', 'PHI-H3LI', 30, NULL, 3, NULL),
(319, 'Littérature française', 'LIT-H3LI', 30, 3, 4, NULL),
(320, 'Histoire', 'HIS-H3LI', 30, 5, 2, NULL),
(321, 'Géographie', 'GEO-H3LI', 30, 5, 2, NULL),
(322, 'Français', 'FRA-H4LI', 31, 3, 4, NULL),
(323, 'Anglais', 'ANG-H4LI', 31, 3, 3, NULL),
(324, 'Éducation civique et morale', 'ECM-H4LI', 31, 5, 1, NULL),
(325, 'Éducation physique', 'EPH-H4LI', 31, NULL, 2, NULL),
(326, 'Informatique', 'INF-H4LI', 31, NULL, 2, NULL),
(327, 'Éducation religieuse', 'REL-H4LI', 31, NULL, 1, NULL),
(328, 'Latin', 'LAT-H4LI', 31, 3, 4, NULL),
(329, 'Philosophie', 'PHI-H4LI', 31, NULL, 3, NULL),
(330, 'Littérature française', 'LIT-H4LI', 31, 3, 4, NULL),
(331, 'Histoire', 'HIS-H4LI', 31, 5, 2, NULL),
(332, 'Géographie', 'GEO-H4LI', 31, 5, 2, NULL),
(333, 'Français', 'FRA-H1EL', 32, 3, 4, NULL),
(334, 'Anglais', 'ANG-H1EL', 32, 3, 3, NULL),
(335, 'Éducation civique et morale', 'ECM-H1EL', 32, 5, 1, NULL),
(336, 'Éducation physique', 'EPH-H1EL', 32, NULL, 2, NULL),
(337, 'Informatique', 'INF-H1EL', 32, NULL, 2, NULL),
(338, 'Éducation religieuse', 'REL-H1EL', 32, NULL, 1, NULL),
(339, 'Électricité (théorie)', 'ELE-H1EL', 32, NULL, 5, NULL),
(340, 'Atelier d''électricité', 'ATE-H1EL', 32, NULL, 5, NULL),
(341, 'Dessin technique', 'DTE-H1EL', 32, NULL, 3, NULL),
(342, 'Technologie', 'TEC-H1EL', 32, NULL, 3, NULL),
(343, 'Mathématiques', 'MAT-H1EL', 32, 1, 4, NULL),
(344, 'Physique', 'PHY-H1EL', 32, 2, 3, NULL),
(345, 'Français', 'FRA-H2EL', 33, 3, 4, NULL),
(346, 'Anglais', 'ANG-H2EL', 33, 3, 3, NULL),
(347, 'Éducation civique et morale', 'ECM-H2EL', 33, 5, 1, NULL),
(348, 'Éducation physique', 'EPH-H2EL', 33, NULL, 2, NULL),
(349, 'Informatique', 'INF-H2EL', 33, NULL, 2, NULL),
(350, 'Éducation religieuse', 'REL-H2EL', 33, NULL, 1, NULL),
(351, 'Électricité (théorie)', 'ELE-H2EL', 33, NULL, 5, NULL),
(352, 'Atelier d''électricité', 'ATE-H2EL', 33, NULL, 5, NULL),
(353, 'Dessin technique', 'DTE-H2EL', 33, NULL, 3, NULL),
(354, 'Technologie', 'TEC-H2EL', 33, NULL, 3, NULL),
(355, 'Mathématiques', 'MAT-H2EL', 33, 1, 4, NULL),
(356, 'Physique', 'PHY-H2EL', 33, 2, 3, NULL),
(357, 'Français', 'FRA-H3EL', 34, 3, 4, NULL),
(358, 'Anglais', 'ANG-H3EL', 34, 3, 3, NULL),
(359, 'Éducation civique et morale', 'ECM-H3EL', 34, 5, 1, NULL),
(360, 'Éducation physique', 'EPH-H3EL', 34, NULL, 2, NULL),
(361, 'Informatique', 'INF-H3EL', 34, NULL, 2, NULL),
(362, 'Éducation religieuse', 'REL-H3EL', 34, NULL, 1, NULL),
(363, 'Électricité (théorie)', 'ELE-H3EL', 34, NULL, 5, NULL),
(364, 'Atelier d''électricité', 'ATE-H3EL', 34, NULL, 5, NULL),
(365, 'Dessin technique', 'DTE-H3EL', 34, NULL, 3, NULL),
(366, 'Technologie', 'TEC-H3EL', 34, NULL, 3, NULL),
(367, 'Mathématiques', 'MAT-H3EL', 34, 1, 4, NULL),
(368, 'Physique', 'PHY-H3EL', 34, 2, 3, NULL),
(369, 'Français', 'FRA-H4EL', 35, 3, 4, NULL),
(370, 'Anglais', 'ANG-H4EL', 35, 3, 3, NULL),
(371, 'Éducation civique et morale', 'ECM-H4EL', 35, 5, 1, NULL),
(372, 'Éducation physique', 'EPH-H4EL', 35, NULL, 2, NULL),
(373, 'Informatique', 'INF-H4EL', 35, NULL, 2, NULL),
(374, 'Éducation religieuse', 'REL-H4EL', 35, NULL, 1, NULL),
(375, 'Électricité (théorie)', 'ELE-H4EL', 35, NULL, 5, NULL),
(376, 'Atelier d''électricité', 'ATE-H4EL', 35, NULL, 5, NULL),
(377, 'Dessin technique', 'DTE-H4EL', 35, NULL, 3, NULL),
(378, 'Technologie', 'TEC-H4EL', 35, NULL, 3, NULL),
(379, 'Mathématiques', 'MAT-H4EL', 35, 1, 4, NULL),
(380, 'Physique', 'PHY-H4EL', 35, 2, 3, NULL);

-- ---------------------------------------------------------------------
-- TYPES DE FRAIS  (montants indicatifs en USD ; ajustez selon votre école)
-- frequency : one_time = une fois | term = par trimestre | yearly = par an
-- ---------------------------------------------------------------------
INSERT INTO `fee_types` (`id`, `name`, `amount`, `frequency`, `description`, `status`) VALUES
(1, 'Minerval (frais de scolarité)', 30.0, 'term', 'Frais de scolarité par trimestre', 1),
(2, 'Frais d''inscription', 20.0, 'one_time', 'Frais de première inscription', 1),
(3, 'Frais d''examen', 5.0, 'term', 'Frais d''examen du trimestre', 1),
(4, 'Carte d''élève et bulletin', 3.0, 'yearly', 'Carte d''élève et bulletins de l''année', 1),
(5, 'Tenue scolaire', 15.0, 'one_time', 'Uniforme scolaire', 1);

-- ---------------------------------------------------------------------
-- FACTURES ET PAIEMENTS (module Frais scolaires)
-- ---------------------------------------------------------------------
INSERT INTO `fees` (`id`, `invoice_no`, `student_id`, `fee_type_id`, `amount`, `discount_type`, `discount_amount`, `scholarship`, `due_date`, `status`) VALUES
(1, 'INV-2026-0001', 1, 2, 20.0, 'none', 0.0, 0.0, '2026-09-15', 'paid'),
(2, 'INV-2026-0002', 1, 1, 30.0, 'none', 0.0, 0.0, '2026-10-15', 'paid'),
(3, 'INV-2026-0003', 2, 1, 30.0, 'none', 0.0, 0.0, '2026-10-15', 'pending'),
(4, 'INV-2026-0004', 2, 2, 20.0, 'percent', 10.0, 0.0, '2026-09-15', 'pending'),
(5, 'INV-2026-0005', 3, 1, 30.0, 'none', 0.0, 0.0, '2026-10-15', 'pending');

INSERT INTO `payments` (`id`, `receipt_no`, `fee_id`, `student_id`, `amount`, `payment_method`, `transaction_id`, `paid_by`, `payment_date`, `remark`) VALUES
(1, 'RCP-2026-0001', 2, 1, 30.0, 'mobile_money', 'MPESA-77120', 1, '2026-10-05 10:15:00', 'Minerval du 1er trimestre'),
(2, 'RCP-2026-0002', 1, 1, 20.0, 'cash', 'CASH-001', 1, '2026-09-10 09:30:00', 'Frais d''inscription');

-- ---------------------------------------------------------------------
-- TYPES D'ÉVALUATION ET EXAMENS
-- ---------------------------------------------------------------------
INSERT INTO `exam_types` (`id`, `name`, `description`) VALUES
(1, 'Période', 'Évaluation de période (interrogations et travaux journaliers)'),
(2, 'Examen de semestre', 'Examen de fin de semestre'),
(3, 'Examen trimestriel', 'Examen de fin de trimestre'),
(4, 'TENAFEP blanc', 'Test national de fin d''études primaires (examen blanc)'),
(5, 'Examen d''État blanc', 'Examen blanc préparatoire à l''Examen d''État');

INSERT INTO `exams` (`id`, `name`, `exam_type_id`, `class_id`, `session_id`, `start_date`, `end_date`, `status`) VALUES
(1, '1er trimestre 2026 - 1ère Primaire', 3, 4, 1, '2026-12-07', '2026-12-11', 'upcoming'),
(2, 'Examen de semestre 2027 - 4ème Humanités Mathématiques-Physique', 2, 19, 1, '2027-01-18', '2027-01-29', 'upcoming');

-- Épreuve terminée : les élèves/enseignants de démonstration ont des résultats à consulter.
INSERT INTO `exams` (`id`, `name`, `exam_type_id`, `subject_id`, `class_id`, `section_id`, `session_id`, `term`, `start_date`, `end_date`, `status`) VALUES
(3, '1ère période - Mathématiques - 2ème Primaire', 1, 30, 5, 6, 1, '1er trimestre', '2026-10-12', '2026-10-16', 'completed');

-- ---------------------------------------------------------------------
-- RÉSULTATS D'EXEMPLE (pourcentages ; mention selon le barème)
-- ---------------------------------------------------------------------
INSERT INTO `results` (`exam_id`, `student_id`, `subject_id`, `marks_obtained`, `max_marks`, `grade`, `remarks`, `status`) VALUES
(1, 1, 20, 88.0, 100.0, 'TB', 'Très bien', 'draft'),
(1, 1, 19, 76.0, 100.0, 'B', 'Bien', 'draft'),
(1, 1, 21, 92.0, 100.0, 'E', 'Excellent', 'draft'),
(1, 2, 20, 64.0, 100.0, 'AB', 'Assez bien', 'draft'),
(1, 2, 19, 81.0, 100.0, 'TB', 'Très bien', 'draft'),
(1, 2, 21, 55.0, 100.0, 'S', 'Satisfaisant', 'draft'),
(1, 3, 20, 70.0, 100.0, 'B', 'Bien', 'draft'),
(1, 3, 19, 60.0, 100.0, 'AB', 'Assez bien', 'draft'),
(1, 3, 21, 74.0, 100.0, 'B', 'Bien', 'draft'),
(3, 6, 30, 81.0, 100.0, 'TB', 'Très bien', 'published'),
(3, 7, 30, 85.0, 100.0, 'TB', 'Très bien', 'published');

-- ---------------------------------------------------------------------
-- PERSONNEL ADMINISTRATIF ET SALAIRES (USD)
-- ---------------------------------------------------------------------
INSERT INTO `staff` (`id`, `user_id`, `employee_id`, `first_name`, `last_name`, `department`, `designation`, `phone`, `email`, `joining_date`, `basic_salary`, `status`) VALUES
(1, NULL, 'STF-0001', 'Linda', 'Mulumba', 'Comptabilité', 'Comptable', '+243 81 000 0105', 'comptabilite@school.test', '2021-01-15', 220.0, 'active');

INSERT INTO `salary` (`staff_id`, `teacher_id`, `month`, `year`, `basic_salary`, `allowances`, `deductions`, `net_salary`, `payment_date`, `status`) VALUES
(1, NULL, 10, 2026, 220.0, 20.0, 10.0, 230.0, '2026-10-30', 'paid'),
(NULL, 1, 10, 2026, 250.0, 30.0, 15.0, 265.0, '2026-10-30', 'paid');

-- ---------------------------------------------------------------------
-- PRÉSENCES (derniers jours de classe)
-- ---------------------------------------------------------------------
INSERT INTO `attendance` (`student_id`, `class_id`, `section_id`, `date`, `status`, `taken_by`) VALUES
(1, 4, 4, CURDATE() - INTERVAL 4 DAY, 'present', 1),
(2, 4, 4, CURDATE() - INTERVAL 4 DAY, 'present', 1),
(3, 4, 5, CURDATE() - INTERVAL 4 DAY, 'late', 1),
(1, 4, 4, CURDATE() - INTERVAL 3 DAY, 'present', 1),
(2, 4, 4, CURDATE() - INTERVAL 3 DAY, 'absent', 1),
(3, 4, 5, CURDATE() - INTERVAL 3 DAY, 'present', 1),
(1, 4, 4, CURDATE() - INTERVAL 2 DAY, 'leave', 1),
(2, 4, 4, CURDATE() - INTERVAL 2 DAY, 'present', 1),
(3, 4, 5, CURDATE() - INTERVAL 2 DAY, 'present', 1),
(1, 4, 4, CURDATE() - INTERVAL 1 DAY, 'present', 1),
(2, 4, 4, CURDATE() - INTERVAL 1 DAY, 'present', 1),
(3, 4, 5, CURDATE() - INTERVAL 1 DAY, 'absent', 1),
(1, 4, 4, CURDATE(), 'present', 1),
(2, 4, 4, CURDATE(), 'present', 1),
(3, 4, 5, CURDATE(), 'late', 1),
(7, 5, 6, CURDATE() - INTERVAL 2 DAY, 'present', 4),
(7, 5, 6, CURDATE() - INTERVAL 1 DAY, 'present', 4),
(7, 5, 6, CURDATE(), 'late', 4);

INSERT INTO `teacher_attendance` (`teacher_id`, `date`, `status`) VALUES
(1, CURDATE(), 'present'),
(2, CURDATE(), 'present'),
(3, CURDATE(), 'leave');

-- ---------------------------------------------------------------------
-- TRANSPORT SCOLAIRE
-- ---------------------------------------------------------------------
INSERT INTO `drivers` (`id`, `name`, `license_no`, `phone`, `status`) VALUES
(1, 'Patrick Mulamba', 'PC-556677', '+243 81 000 0120', 'active'),
(2, 'Fidèle Tshimanga', 'PC-889900', '+243 81 000 0121', 'active');

INSERT INTO `vehicles` (`id`, `vehicle_no`, `model`, `capacity`, `driver_id`, `status`) VALUES
(1, 'BUS-01', 'Toyota Coaster', 30, 1, 'active'),
(2, 'MIN-02', 'Toyota HiAce', 14, 2, 'active');

INSERT INTO `routes` (`id`, `name`, `stops`, `fare`, `status`) VALUES
(1, 'Circuit Gombe - Ngaliema', 'Gombe, Kintambo, Ma Campagne, Ngaliema', 15.0, 1),
(2, 'Circuit Limete - Lemba', 'Limete, Matete, Lemba, Righini', 18.0, 1);

INSERT INTO `transport_assignments` (`id`, `student_id`, `route_id`, `vehicle_id`, `pickup_point`, `amount`, `status`) VALUES
(1, 1, 1, 1, 'Av. de la Libération (Mbinza)', 15.0, 'active'),
(2, 2, 2, 2, 'Av. Kasa-Vubu (Kalamu)', 18.0, 'active');

-- ---------------------------------------------------------------------
-- INVENTAIRE
-- ---------------------------------------------------------------------
INSERT INTO `inventory_categories` (`id`, `name`) VALUES
(1, 'Mobilier'),
(2, 'Matériel informatique'),
(3, 'Sport'),
(4, 'Fournitures');

INSERT INTO `suppliers` (`id`, `name`, `contact_person`, `phone`, `email`, `address`) VALUES
(1, 'Papeterie Centrale', 'Alain Kabeya', '+243 81 000 0130', 'ventes@papeterie.test', '25, Av. du Commerce, Gombe');

INSERT INTO `inventory_items` (`id`, `name`, `category_id`, `supplier_id`, `quantity`, `unit_price`, `location`, `status`) VALUES
(1, 'Banc-pupitre (2 places)', 1, 1, 120, 25.0, 'Dépôt A', 'in_stock'),
(2, 'Ordinateur portable', 2, 1, 18, 450.0, 'Salle informatique', 'in_stock'),
(3, 'Ballon de football', 3, NULL, 30, 12.0, 'Dépôt de sport', 'in_stock');

-- ---------------------------------------------------------------------
-- ÉVÉNEMENTS ET JOURS FÉRIÉS (RDC)
-- ---------------------------------------------------------------------
INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `event_time`, `event_type`, `location`, `created_by`) VALUES
(1, 'Journée sportive', 'Compétition d''athlétisme entre les classes', DATE_ADD(CURDATE(), INTERVAL 14 DAY), '09:00:00', 'sport', 'Cour de l''école', 1),
(2, 'Réunion du personnel', 'Réunion mensuelle de coordination', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '15:00:00', 'meeting', 'Salle des professeurs', 1);

INSERT INTO `holidays` (`title`, `start_date`, `end_date`, `description`) VALUES
('Fête du Travail', '2027-05-01', '2027-05-01', 'Jour férié national'),
('Fête de l''Indépendance', '2027-06-30', '2027-06-30', 'Jour férié national (30 juin)'),
('Fête des Parents', '2026-08-01', '2026-08-01', 'Jour férié national'),
('Fête de Noël', '2026-12-25', '2026-12-25', 'Jour férié'),
('Vacances de Noël et Nouvel An', '2026-12-21', '2027-01-03', 'Congé scolaire'),
('Vacances de Pâques', '2027-03-29', '2027-04-09', 'Congé scolaire'),
('Journée des Martyrs de l''Indépendance', '2027-01-04', '2027-01-04', 'Jour férié (4 janvier)'),
('Anniversaire de la Libération', '2027-05-17', '2027-05-17', 'Jour férié (17 mai)');

INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`) VALUES
(1, 'Bienvenue', 'Bienvenue sur le système de gestion de l''école', 'info'),
(3, 'Horaire mis à jour', 'Votre horaire de la 1ère Primaire a été mis à jour', 'warning'),
(4, 'Avis d''examen', 'Les examens du 1er trimestre commencent bientôt. Consultez le calendrier.', 'info');

INSERT INTO `messages` (`sender_id`, `receiver_id`, `subject`, `body`, `is_read`) VALUES
(1, 4, 'Avis d''examen', 'Les examens du 1er trimestre commencent bientôt. Consultez le calendrier des examens.', 0);

INSERT INTO `discipline_records` (`student_id`, `incident_date`, `type`, `severity`, `description`, `sanction`, `parent_notified`, `status`, `recorded_by`) VALUES
(5, CURDATE() - INTERVAL 2 DAY, 'Phone in class', 'moderate', 'Téléphone utilisé pendant le cours de mathématiques.', 'Warning', 1, 'open', 13),
(9, CURDATE() - INTERVAL 5 DAY, 'Late arrival', 'minor', 'Troisième retard de la semaine.', 'Detention', 0, 'open', 13),
(3, CURDATE() - INTERVAL 9 DAY, 'Fight', 'serious', 'Bagarre dans la cour pendant la récréation.', 'Parents summoned', 1, 'resolved', 13);

-- ---------------------------------------------------------------------
-- PARAMÈTRES DE L'ÉCOLE
-- ---------------------------------------------------------------------
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('school_name', 'Complexe Scolaire Les Étoiles du Congo'),
('school_short_name', 'CSEC'),
('school_address', '15, Avenue de la Libération, Commune de Ngaliema, Kinshasa, RDC'),
('school_phone', '+243 81 000 1234'),
('school_email', 'info@csec.test'),
('school_website', 'https://www.csec.test'),
('school_logo', ''),
('school_motto', 'Savoir, Intégrité, Excellence'),
('principal_name', 'Mme Espérance Mbuyi'),
('school_favicon', ''),
('academic_year', '2026 - 2027'),
('currency', 'USD'),
('currency_symbol', '$'),
('currency_position', 'after'),
('timezone', 'Africa/Kinshasa'),
('language', 'fr'),
('academic_session_id', '1'),
('fee_due_days', '15'),
('attendance_cutoff_time', '07:30'),
('dark_mode_default', '0');

INSERT INTO `activity_logs` (`user_id`, `action`, `module`, `description`, `ip_address`) VALUES
(1, 'install', 'system', 'Système installé et base de données initialisée (RDC)', '127.0.0.1');

-- ---------------------------------------------------------------------
-- BULLETINS : schémas d'évaluation 2026-2027 et notes d'exemple (1ère Primaire, 1er trimestre)
-- ---------------------------------------------------------------------
INSERT INTO `school_evaluation_schemes` (`school_id`, `level_code`, `academic_year_id`, `scheme_id`, `version`, `config_json`, `created_by`) VALUES
(1, 'primaire',   1, 'rdc-primaire',   1, '{"schema_version": 1, "scheme_id": "rdc-primaire", "label": "RDC - Primaire et maternelle", "level": ["maternelle", "primaire"], "periods_per_year": 6, "terms": [{"id": "T1", "label": "1er trimestre", "steps": [{"id": "P1", "type": "period", "label": "1ère période", "max_points": 10, "weight": 1}, {"id": "P2", "type": "period", "label": "2ème période", "max_points": 10, "weight": 1}, {"id": "EX1", "type": "exam", "label": "Examen", "max_points": 20, "weight": 1}, {"id": "TOTAL_T1", "type": "total", "label": "Total trimestriel", "formula_id": "term_total"}]}, {"id": "T2", "label": "2ème trimestre", "steps": [{"id": "P3", "type": "period", "label": "3ème période", "max_points": 10, "weight": 1}, {"id": "P4", "type": "period", "label": "4ème période", "max_points": 10, "weight": 1}, {"id": "EX2", "type": "exam", "label": "Examen", "max_points": 20, "weight": 1}, {"id": "TOTAL_T2", "type": "total", "label": "Total trimestriel", "formula_id": "term_total"}]}, {"id": "T3", "label": "3ème trimestre", "steps": [{"id": "P5", "type": "period", "label": "5ème période", "max_points": 10, "weight": 1}, {"id": "P6", "type": "period", "label": "6ème période", "max_points": 10, "weight": 1}, {"id": "EX3", "type": "exam", "label": "Examen", "max_points": 20, "weight": 1}, {"id": "TOTAL_T3", "type": "total", "label": "Total trimestriel", "formula_id": "term_total"}]}], "customizable": ["labels", "max_points", "weights", "formulas", "display_order"]}', 1),
(1, 'secondaire', 1, 'rdc-secondaire', 1, '{"schema_version": 1, "scheme_id": "rdc-secondaire", "label": "RDC - Secondaire", "level": ["secondaire"], "terms": [{"id": "S1", "label": "1er semestre", "steps": [{"id": "P1", "type": "period", "label": "Première période", "max_points": 20, "weight": 1}, {"id": "P2", "type": "period", "label": "Deuxième période", "max_points": 20, "weight": 1}, {"id": "EX1", "type": "exam", "label": "Examen", "max_points": 40, "weight": 1}, {"id": "TOTAL_S1", "type": "total", "label": "Total 1er semestre", "formula_id": "semester_total"}]}, {"id": "S2", "label": "2ème semestre", "steps": [{"id": "P3", "type": "period", "label": "Troisième période", "max_points": 20, "weight": 1}, {"id": "P4", "type": "period", "label": "Quatrième période", "max_points": 20, "weight": 1}, {"id": "EX2", "type": "exam", "label": "Examen", "max_points": 40, "weight": 1}, {"id": "TOTAL_S2", "type": "total", "label": "Total 2ème semestre", "formula_id": "semester_total"}]}], "final_steps": [{"id": "TOTAL_GENERAL", "type": "total", "label": "Total général", "formula_id": "year_total"}], "customizable": ["labels", "max_points", "weights", "formulas", "display_order"]}', 1);

INSERT INTO `student_marks` (`school_id`, `student_id`, `class_id`, `subject_id`, `academic_year_id`, `scheme_version_id`, `step_id`, `max_points`, `points_obtained`, `entered_by`)
SELECT 1, s.id, s.class_id, sb.id, 1,
       (SELECT id FROM school_evaluation_schemes WHERE level_code = 'primaire' AND academic_year_id = 1 ORDER BY version DESC LIMIT 1),
       st.step_id, st.max_points,
       ROUND(st.max_points * (0.55 + MOD(s.id * 7 + sb.id * 3 + st.n, 40) / 100), 1), 9
FROM students s
INNER JOIN subjects sb ON sb.class_id = s.class_id
INNER JOIN (SELECT 'P1' AS step_id, 10 AS max_points, 1 AS n UNION ALL SELECT 'P2', 10, 2 UNION ALL SELECT 'EX1', 20, 3) st
WHERE s.class_id = 4 AND s.status = 'active';

SET FOREIGN_KEY_CHECKS = 1;


-- ===== database/seeds/demo_levels.sql =====
-- Données de démonstration des modules par niveau (prérequis : migrations 0001-0004 + demo_data.sql)
SET NAMES utf8mb4;

INSERT INTO `user_establishments` (`user_id`, `establishment_id`) VALUES
(15, 1), (15, 2),            -- directeur d'école : maternel + primaire
(11, 3), (12, 3), (13, 3),   -- préfet, directeur des études, directeur de discipline : secondaire
(9, 2), (4, 3), (14, 1), (14, 2), (14, 3);  -- enseignants (primaire, secondaire) ; caissière : tous les établissements

INSERT INTO `student_enrollments` (`student_id`, `class_id`, `section_id`, `establishment_id`, `academic_year_id`, `enrolled_on`, `status`)
SELECT s.id, s.class_id, s.section_id, c.establishment_id, 1, COALESCE(s.admission_date, '2026-09-07'), 'enrolled'
FROM students s INNER JOIN classes c ON c.id = s.class_id WHERE s.class_id IS NOT NULL;

-- Maternel : élève 8 (1ère maternelle)
INSERT INTO `development_assessments` (`student_id`, `domain_id`, `academic_year_id`, `period_id`, `level`, `comment`, `assessed_by`)
SELECT 8, d.id, 1, 'T1', CASE d.code WHEN 'LANG' THEN 'in_progress' WHEN 'MOT' THEN 'acquired' WHEN 'SOC' THEN 'acquired' WHEN 'ART' THEN 'acquired' ELSE 'in_progress' END, NULL, 4
FROM development_domains d;
INSERT INTO `child_health_records` (`student_id`, `vaccinations`, `allergies`, `medical_notes`, `emergency_contact_name`, `emergency_contact_phone`)
VALUES (8, 'Carnet de vaccination à jour (BCG, polio, rougeole)', 'Aucune connue', 'RAS', 'Nadine Lokua', '+243 81 000 0125');
INSERT INTO `authorized_pickups` (`student_id`, `full_name`, `relation`, `phone`, `id_document`) VALUES
(8, 'Nadine Lokua', 'Mère', '+243 81 000 0125', 'Carte d''électeur'),
(8, 'Papy Lokua', 'Oncle', '+243 81 000 0140', 'Carte d''électeur');
INSERT INTO `daily_reports` (`class_id`, `section_id`, `report_date`, `activities`, `meals`, `nap_minutes`, `notes_for_parents`, `created_by`)
SELECT c.id, s.id, CURDATE(), 'Chant, dessin libre, jeux de construction', 'Bouillie et fruit', 60, 'Pensez à apporter une tenue de rechange.', 4
FROM classes c INNER JOIN sections s ON s.class_id = c.id WHERE c.name = '1ère Maternelle' LIMIT 1;

-- Conduite (secondaire) : élève 9
INSERT INTO `conduct_grades` (`student_id`, `academic_year_id`, `period_id`, `score`, `mention`, `incident_count`) VALUES (9, 1, 'S1', 92.00, 'Très bien', 1);

-- Barèmes de frais (USD) par établissement
INSERT INTO `fee_plans` (`establishment_id`, `academic_year_id`, `class_id`, `fee_type`, `amount`, `frequency`) VALUES
(1, 1, NULL, 'Minerval', 25.00, 'term'), (1, 1, NULL, 'Frais d''inscription', 15.00, 'one_time'),
(2, 1, NULL, 'Minerval', 30.00, 'term'), (2, 1, NULL, 'Frais d''inscription', 20.00, 'one_time'), (2, 1, NULL, 'Frais d''examen', 5.00, 'term'),
(3, 1, NULL, 'Minerval', 45.00, 'term'), (3, 1, NULL, 'Frais d''inscription', 25.00, 'one_time'), (3, 1, NULL, 'Frais d''examen', 8.00, 'semester');


-- ===== database/seeds/license_demo.sql =====
-- Licence de DÉMONSTRATION (signée avec la clé de développement ; valable jusqu'au 2027-08-31 + 30 jours de grâce).
-- À remplacer par une vraie licence émise avec VOTRE clé (tools/license_tool.py issue).
INSERT INTO `licenses` (`license_id`, `token`, `holder`, `valid_from`, `valid_until`, `status`) VALUES
('LIC-DEMO-2026', 'CCL1.eyJ2IjoxLCJsaWNlbnNlX2lkIjoiTElDLURFTU8tMjAyNiIsImhvbGRlciI6IkNvbXBsZXhlIFNjb2xhaXJlIExlcyDDiXRvaWxlcyBkdSBDb25nbyAoRMOJTU8pIiwiaXNzdWVkX2F0IjoiMjAyNi0wOS0wMSIsInZhbGlkX2Zyb20iOiIyMDI2LTA5LTAxIiwidmFsaWRfdW50aWwiOiIyMDI3LTA4LTMxIiwiZ3JhY2VfZGF5cyI6MzAsImVkaXRpb24iOiJzdGFuZGFyZCIsImxldmVscyI6WyJtYXRlcm5lbCIsInByaW1haXJlIiwic2Vjb25kYWlyZSJdLCJtb2R1bGVzIjpbIioiXSwibWF4X3N0dWRlbnRzIjp7Im1hdGVybmVsIjoxMDAsInByaW1haXJlIjo1MDAsInNlY29uZGFpcmUiOjgwMH19.fZ8ibw2vYefYOCALOBVRTOE1FakRSJWAK1uDSRIBfi_bd3DSuteElM2ygUTcg_o-yH1dm65oiZ-3cRpTMP_yCw', 'Complexe Scolaire Les Étoiles du Congo (DÉMO)', '2026-09-01', '2027-08-31', 'active');
