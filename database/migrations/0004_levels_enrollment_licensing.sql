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
