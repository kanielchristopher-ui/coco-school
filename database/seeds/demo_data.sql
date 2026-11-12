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
