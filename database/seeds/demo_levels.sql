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
