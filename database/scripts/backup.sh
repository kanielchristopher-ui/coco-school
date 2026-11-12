#!/bin/sh
# Sauvegarde complète de la base (à lancer AVANT toute migration).
#   DB_NAME=school_management DB_USER=root DB_PASS= sh database/scripts/backup.sh
set -e
DB_NAME="${DB_NAME:-school_management}"; DB_USER="${DB_USER:-root}"; DB_HOST="${DB_HOST:-127.0.0.1}"
OUT="${BACKUP_DIR:-./backups}"; mkdir -p "$OUT"
FILE="$OUT/${DB_NAME}_$(date +%Y%m%d_%H%M%S).sql"
mysqldump --single-transaction --routines --default-character-set=utf8mb4 -h "$DB_HOST" -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} "$DB_NAME" > "$FILE"
echo "Sauvegarde : $FILE ($(wc -c < "$FILE") octets)"
