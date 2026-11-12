#!/bin/sh
# Lance TOUTES les vérifications disponibles. Les tests PHP nécessitent PHP ≥ 8.1 (+ sodium recommandé, pdo_sqlite facultatif).
set -e
cd "$(dirname "$0")/.."
echo "== Schéma SQL (statique) =="; python3 tools/check_schema.py
echo "== Modèles à jour avec les migrations =="; cp -r core/Domain /tmp/domain_before; python3 tools/generate_entities.py >/dev/null; diff -r /tmp/domain_before core/Domain >/dev/null && echo "OK" || { echo "ÉCHEC : relancer tools/generate_entities.py et committer"; exit 1; }
echo "== Fichier SQL unique à jour =="; python3 tools/build_sql.py >/dev/null; git diff --quiet database/school_management.sql 2>/dev/null && echo "OK" || echo "(modifié par la régénération)"
echo "== Licences (format + états) =="; python3 tools/test_license_tool.py
if command -v php >/dev/null 2>&1; then echo "== Tests PHP du Core =="; php tests/run.php; else echo "!! PHP absent : tests PHP non exécutés (php tests/run.php)"; fi
