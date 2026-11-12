# Stratégie hors-ligne, synchronisation et serveur LAN

## Cible
Un serveur LAN **par établissement** (PHP + MySQL/MariaDB ou SQLite) reste la source de vérité locale ; les postes/appareils fonctionnent même si Internet est coupé. Une synchronisation cloud optionnelle consolide plusieurs établissements.

## Principes
1. **Journal d'opérations** (`sync_log`) : chaque écriture du Core produit une entrée `(id, school_id, device_id, entity, entity_id, op, payload_json, lamport, created_at)`.
2. **Curseur** : un appareil demande `changes?since=<id>` et envoie ses opérations locales en lot (`push`).
3. **Conflits** : par entité — *notes* : dernière écriture par (élève, cours, étape) avec horodatage logique, conflit signalé si deux appareils modifient la même note ; *bulletins publiés* : jamais écrasés (nouvelle version) ; *paiements* : **append-only** (les annulations sont des opérations, pas des suppressions) ; *identifiants* : UUID générés côté appareil pour éviter les collisions.
4. **Sauvegardes automatiques** : `backup.sh` planifié (cron/tâche planifiée), rétention 14 jours, copie sur disque externe.
5. **Sécurité LAN** : HTTPS (certificat interne), liste d'appareils autorisés, jetons à durée courte, limitation de débit, journal d'audit.
6. **Mises à jour** : `migrate.php migrate` après sauvegarde ; refus d'une migration modifiée.

## Prérequis dans le Core (à faire)
Colonne `school_id` généralisée, UUID, table `sync_log`, écouteur d'événements `*` qui alimente le journal.
