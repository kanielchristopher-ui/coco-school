# API REST (contrat pour `server/` — non implémentée)

Base : `https://<serveur-lan>/api/v1` · JSON UTF-8 · authentification : jeton d'appareil + jeton utilisateur (`Authorization: Bearer …`).
Chaque route appelle un service du Core avec l'`Actor` du jeton ; les erreurs du Core sont traduites : `ValidationException` → 422 `{errors}`, `PermissionDeniedException` → 403, `NotFoundException` → 404, `BusinessRuleException` → 409.

| Méthode | Route | Service Core |
|---|---|---|
| POST | /auth/login | `AuthService::attempt` |
| GET | /bulletins?class_id&period | (à créer) liste |
| POST | /bulletins/{id}/validate · /publish | `BulletinWorkflow` |
| GET | /students/{id}/bulletins | bulletins publiés de l'élève |
| PUT | /marks | saisie de notes |
| POST | /fees/{id}/collections | `PaymentService::collect` |
| GET | /sync/changes?since=<cursor> · POST /sync/push | synchronisation (voir SYNCHRONISATION_LAN.md) |

Chaque réponse d'écriture renvoie l'identifiant et la version de l'objet. Pagination : `?page=&per_page=`.
