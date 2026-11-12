#!/usr/bin/env python3
"""Assemble database/school_management.sql (fichier unique) = migrations/*.sql + seeds/*.sql, dans l'ordre.
   Usage : python3 tools/build_sql.py"""
import glob, os, re
R = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
parts = ["-- COCO SCHOOL — installation complète en UN fichier (migrations 0001..N + données de démonstration).\n"
         "-- Production : appliquer database/migrations/*.sql (php tools/migrate.php migrate) SANS les fichiers de database/seeds/.\n"
         "-- Fichier généré par tools/build_sql.py : ne pas modifier à la main.\n"
         "SET NAMES utf8mb4;\nCREATE DATABASE IF NOT EXISTS `school_management` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\nUSE `school_management`;\n"]
for f in sorted(glob.glob(R + '/database/migrations/*.sql')) + [R + '/database/seeds/demo_data.sql', R + '/database/seeds/demo_levels.sql'] + sorted(glob.glob(R + '/database/seeds/license_demo.sql')):
    if os.path.isfile(f):
        parts.append('\n-- ===== %s =====\n' % os.path.relpath(f, R) + open(f, encoding='utf-8').read())
out = '\n'.join(parts)
open(R + '/database/school_management.sql', 'w', encoding='utf-8').write(out)
print('school_management.sql', len(out), 'octets')
