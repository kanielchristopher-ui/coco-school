# Licences : principe, validité, exploitation

## Principe
Une licence est une **clé signée** (`CCL1.<contenu>.<signature>`, Ed25519) émise par l'éditeur avec **sa clé privée** (jamais sur le serveur du client).
Le logiciel ne contient que la **clé publique** (`core/Licensing/trusted_keys.php`) : il vérifie la clé **hors ligne**. Modifier une date ou un plafond invalide la signature.

Contenu : titulaire, `valid_from`, `valid_until`, `grace_days`, niveaux couverts (`maternel`, `primaire`, `secondaire`), modules (`*` = tous), plafond d'élèves actifs par niveau (0 = illimité), édition.

## États (`LicenseStatus`)
| État | Écriture | Lecture | Quand |
|---|---|---|---|
| `valid` | oui | oui | dans la période de validité |
| `expiring_soon` | oui | oui | ≤ 30 jours avant la fin (bandeau d'avertissement) |
| `grace` | oui | oui | après la fin, pendant `grace_days` (bandeau) |
| `expired` | **non** | oui | après la grâce : **lecture seule** (POST et suppressions refusés) |
| `not_yet_valid` | non | oui | avant `valid_from` |
| `trial` | oui | oui | aucune clé : essai de 30 jours, 30 élèves max par niveau |
| `trial_expired` | non | oui | essai terminé |
| `invalid_signature`, `malformed`, `revoked`, `tampered` | non | **non** | clé falsifiée/illisible/révoquée, ou horloge reculée : **écran verrouillé** (seule la page Licence reste accessible aux administrateurs) |

## Applications dans le code
* `LicenseManager::requireWrite(niveau, module)` — appelée par les services d'écriture (`PaymentService`, `EnrollmentService`…) : état, niveau couvert, module couvert.
* `LicenseManager::requireQuota(niveau)` — plafond d'élèves à l'inscription / création.
* Web : `LicenseGate` (routeur) verrouille ou passe en lecture seule ; page **Licence** (menu Système) pour voir l'état et installer une clé ; bandeau d'alerte.
* Anti-retour d'horloge : `last_seen` mémorise la date la plus récente observée ; une horloge reculée de plus d'un jour = `tampered`.
* Révocation locale : `system_state.revoked_ids` (liste JSON d'identifiants).
* Journal : table `license_events` (installation, refus, changements d'état).

## Exploitation (éditeur)
```
python3 tools/license_tool.py keygen --out ./cles            # UNE fois ; garder private.key hors du dépôt
# mettre public.key dans core/Licensing/trusted_keys.php
python3 tools/license_tool.py issue --private ./cles/private.key --holder "Complexe X" \
   --from 2026-09-01 --until 2027-08-31 --grace 30 \
   --levels maternel,primaire,secondaire --students maternel=100,primaire=500,secondaire=800
python3 tools/license_tool.py inspect <clé>
```
Le client colle la clé dans **Système → Licence**. Renouvellement = nouvelle clé (l'ancienne passe à `replaced`).

## Limites honnêtes
Une licence logicielle hors ligne dans un code PHP ouvert ne peut pas empêcher un client **déterminé** de modifier le code pour retirer le contrôle : elle sert à encadrer contractuellement l'usage, avec des garde-fous techniques (signature, plafonds, expiration, anti-retour d'horloge). Pour plus de robustesse : encoder/obfusquer le Core, lier la licence à l'installation (identifiant machine), vérification en ligne périodique. La clé de développement fournie doit être **remplacée** avant toute distribution.
