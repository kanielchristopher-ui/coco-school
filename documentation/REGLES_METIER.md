# Règles métier (extraits implémentés dans le Core)

## Bulletins
* Maternel/primaire : 3 trimestres (2 périodes + 1 examen) ; secondaire : 2 semestres. Maxima et poids explicites par étape.
* Total d'une période = Σ (points × poids) ; pourcentage = points / (max × nombre de cours) ; mention par `GradeScale`, réussite ≥ 50 %.
* États : `draft → validated → published → superseded`. Validation exige toutes les notes. Publication par le directeur (maternel/primaire) ou le préfet (secondaire). Le directeur des études peut valider au secondaire.
* Un bulletin publié n'est jamais modifié ; une correction crée une nouvelle version ; la saisie est verrouillée tant qu'il est publié.
* Un enseignant ne saisit que les notes de ses cours ; parents/élèves ne voient que les bulletins publiés qui leur appartiennent.

## Finance (encaissement)
* Seul un rôle ayant `fees.manage` encaisse. Montant > 0, mode ∈ {cash, bank_transfer, cheque, mobile_money, card, online}, date valide.
* Interdit : payer un frais déjà soldé, dépasser le solde restant dû.
* Reçu `RCT-AAAA-NNNN` séquentiel ; statut du frais recalculé (`pending/partial/paid`) ; un frais mensuel n'est soldé que par les paiements de son mois ; les paiements annulés ne comptent pas.
* Chaque encaissement : transaction + audit + événement `payment.recorded`.

## Authentification
* Connexion par nom d'utilisateur ou e-mail ; compte `active` seulement ; message d'erreur identique pour compte inconnu/mauvais mot de passe ; échecs journalisés ; rehash automatique.
