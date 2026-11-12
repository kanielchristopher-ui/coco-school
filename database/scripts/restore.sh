#!/bin/sh
# Restauration : sh database/scripts/restore.sh backups/fichier.sql   (REMPLACE le contenu de la base)
set -e
[ -f "$1" ] || { echo "Usage : sh database/scripts/restore.sh fichier.sql"; exit 1; }
DB_NAME="${DB_NAME:-school_management}"; DB_USER="${DB_USER:-root}"; DB_HOST="${DB_HOST:-127.0.0.1}"
printf "Restaurer %s dans %s ? Les données actuelles seront écrasées. (oui/non) " "$1" "$DB_NAME"; read -r a
[ "$a" = "oui" ] || { echo "Annulé."; exit 1; }
mysql -h "$DB_HOST" -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} "$DB_NAME" < "$1"
echo "Restauration terminée."
