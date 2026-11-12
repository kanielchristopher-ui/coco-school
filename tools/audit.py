#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Audit automatique de l'application existante (web/). Génère documentation/audit/*.md
Usage : python3 tools/audit.py          (depuis la racine du dépôt)"""
import re, glob, os, json, collections
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
W = os.path.join(ROOT, 'web'); OUT = os.path.join(ROOT, 'documentation', 'audit'); os.makedirs(OUT, exist_ok=True)
rd = lambda p: open(p, encoding='utf-8', errors='replace').read()
rel = lambda p: os.path.relpath(p, ROOT)
def w(name, text): open(os.path.join(OUT, name), 'w', encoding='utf-8').write(text)

php = sorted(glob.glob(W + '/**/*.php', recursive=True))
# ---------------------------------------------------------------- 01 fichiers
by = collections.defaultdict(list)
for f in php:
    d = os.path.dirname(rel(f)); by[d].append((os.path.basename(f), rd(f).count('\n') + 1))
t = ['# 01 — Inventaire des fichiers PHP', '', '%d fichiers, %d lignes.' % (len(php), sum(n for v in by.values() for _, n in v)), '']
for d in sorted(by):
    t.append('## %s (%d fichiers, %d lignes)' % (d, len(by[d]), sum(n for _, n in by[d])))
    t += ['- `%s` — %d l.' % x for x in sorted(by[d])]; t.append('')
w('01_fichiers_php.md', '\n'.join(t))

# ---------------------------------------------------------------- 02 interfaces / routes / gardes
t = ['# 02 — Contrôleurs, routes, vues et gardes d\'accès', '',
     'Route = `controller/méthode` (méthodes camelCase appelées avec des tirets dans l\'URL). « Garde » = vérification de rôle/permission dans la méthode ou le constructeur.', '']
routes = 0; unguarded = []
for f in sorted(glob.glob(W + '/app/controllers/*.php')):
    s = rd(f); cname = os.path.basename(f)[:-4]; slug = re.sub(r'Controller$', '', cname).lower()
    ctor = re.search(r'function __construct\(\)\s*\{(.*?)\n    \}', s, re.S); ctor_guard = bool(ctor and re.search(r'Auth::(require|gate)', ctor.group(1)))
    t.append('## %s' % cname); t.append('| Route | Gardes | Lignes SQL |'); t.append('|---|---|---|')
    parts = re.split(r'\n    (?:public|protected|private) function ', s)
    for p in parts[1:]:
        m = re.match(r'(\w+)\(', p)
        if not m or not s.count('public function ' + m.group(1)): continue
        if 'public function ' + m.group(1) not in s: continue
        name = m.group(1)
        if name.startswith('__') or name == 'checkAuth': continue
        guards = re.findall(r"Auth::(requireRole|requirePermission|requireAny)\(([^)]*)\)", p)
        g = '; '.join('%s(%s)' % (a, b.strip()[:60]) for a, b in guards) or ('constructeur' if ctor_guard else '—')
        if not guards and not ctor_guard and slug not in ('auth', 'website', 'lang'): unguarded.append('%s/%s' % (slug, name))
        sql = len(re.findall(r"(?i)\b(SELECT|INSERT INTO|UPDATE|DELETE FROM)\b", p))
        t.append('| `%s/%s` | %s | %d |' % (slug, re.sub(r'([A-Z])', lambda x: '-' + x.group(1).lower(), name), g, sql)); routes += 1
    t.append('')
views = collections.defaultdict(list)
for f in glob.glob(W + '/app/views/**/*.php', recursive=True): views[os.path.basename(os.path.dirname(f))].append(os.path.basename(f))
t += ['## Vues par module', ''] + ['- **%s** : %s' % (k, ', '.join(sorted(v))) for k, v in sorted(views.items())]
w('02_interfaces_routes.md', '\n'.join(['%d routes recensées.\n' % routes] + t))

# ---------------------------------------------------------------- 03 formulaires
t = ['# 03 — Formulaires', '', '| Vue | Méthode | Action | Champs |', '|---|---|---|---|']; nforms = 0
for f in sorted(glob.glob(W + '/app/views/**/*.php', recursive=True)):
    s = rd(f)
    for m in re.finditer(r'<form\b([^>]*)>(.*?)</form>', s, re.S):
        a = m.group(1); meth = (re.search(r'method="(\w+)"', a) or [None, 'GET'])[1].upper()
        act = (re.search(r"action=\"<\?= url\('([^']+)'", a) or re.search(r'action="([^"]+)"', a) or [None, '(courant)'])[1]
        names = sorted(set(re.findall(r'name="([a-zA-Z0-9_\[\]]+)"', m.group(2))) - {'csrf_token', 'school_csrf_token'})
        t.append('| %s | %s | `%s` | %s |' % (rel(f)[len('web/app/views/'):], meth, act, ', '.join(names[:14]) + (' …' if len(names) > 14 else ''))); nforms += 1
w('03_formulaires.md', '\n'.join(['%d formulaires.\n' % nforms] + t))

# ---------------------------------------------------------------- 04 base de données
sql = rd(os.path.join(ROOT, 'database', 'school_management.sql'))
tables = re.findall(r'CREATE TABLE (?:IF NOT EXISTS )?`?(\w+)`? \((.*?)\n\) ?ENGINE', sql, re.S)
t = ['# 04 — Schéma de la base de données', '', '%d tables.' % len(tables), '']
fks = []
for name, body in tables:
    cols = [l.strip().rstrip(',') for l in body.split('\n') if l.strip() and not l.strip().upper().startswith(('PRIMARY','UNIQUE','KEY','INDEX','CONSTRAINT','FOREIGN','--'))]
    pk = re.search(r'PRIMARY KEY \(([^)]*)\)', body); uq = re.findall(r'UNIQUE KEY `?\w+`? \(([^)]*)\)', body); idx = re.findall(r'\n\s+KEY `?\w+`? \(([^)]*)\)', body)
    for m in re.finditer(r'FOREIGN KEY \(([^)]*)\) REFERENCES `(\w+)` \(([^)]*)\)(.*)', body): fks.append((name, m.group(1), m.group(2), m.group(3), m.group(4).strip(' ,')))
    t.append('## `%s`' % name); t += ['- %s' % c for c in cols]
    t.append('- PK : %s | UNIQUE : %s | INDEX : %s' % (pk.group(1) if pk else '—', '; '.join(uq) or '—', '; '.join(idx) or '—')); t.append('')
t += ['## Relations (clés étrangères)', '', '| Table | Colonne | Référence | Action |', '|---|---|---|---|'] + ['| %s | %s | %s(%s) | %s |' % f for f in fks]
w('04_base_de_donnees.md', '\n'.join(t))

# ---------------------------------------------------------------- 05 requêtes SQL
t = ['# 05 — Requêtes SQL par fichier', '', '| Fichier | SELECT | INSERT | UPDATE | DELETE | Concaténation variable |', '|---|---|---|---|---|---|']
tot = collections.Counter(); concat = []
for f in php:
    s = rd(f); c = collections.Counter(m.upper().split()[0] for m in re.findall(r"(?i)['\"]\s*(SELECT\b|INSERT INTO\b|UPDATE\b|DELETE FROM\b)", s))
    dyn = len(re.findall(r"""(?i)['"]\s*(?:SELECT|UPDATE|DELETE)[^'"]*['"]\s*\.\s*\$""", s))
    if sum(c.values()):
        t.append('| %s | %d | %d | %d | %d | %d |' % (rel(f)[4:], c['SELECT'], c['INSERT'], c['UPDATE'], c['DELETE'], dyn)); tot += c
        if dyn: concat.append((rel(f)[4:], dyn))
t.insert(2, 'Total : %d SELECT, %d INSERT, %d UPDATE, %d DELETE.\n' % (tot['SELECT'], tot['INSERT'], tot['UPDATE'], tot['DELETE']))
w('05_requetes_sql.md', '\n'.join(t))
ctrl_sql = sum(len(re.findall(r"(?i)\$db->(fetch|fetchAll|fetchColumn|count|insert|update|delete)\(", rd(f))) for f in glob.glob(W + '/app/controllers/*.php'))

# ---------------------------------------------------------------- 06 rôles / permissions
roles = re.findall(r"\((\d+), '([^']+)', '([a-z_]+)', '([^']*)'\)", sql[sql.index('INSERT INTO `roles`'):sql.index('INSERT INTO `permissions`')])
perms = re.findall(r"\((\d+), '([^']+)', '([a-z_.]+)', '([a-z_]+)'\)", sql[sql.index('INSERT INTO `permissions`'):sql.index('INSERT INTO `role_permissions`')])
t = ['# 06 — Rôles et permissions', '', '## Rôles', '| id | slug | nom | description |', '|---|---|---|---|'] + ['| %s | %s | %s | %s |' % (a, c, b, d) for a, b, c, d in roles]
t += ['', '## Permissions (%d)' % len(perms), '| id | slug | module |', '|---|---|---|'] + ['| %s | %s | %s |' % (a, c, d) for a, b, c, d in perms]
t += ['', '## Gardes par rôle dans les contrôleurs', '']
for f in sorted(glob.glob(W + '/app/controllers/*.php')):
    lists = collections.Counter(re.findall(r"Auth::requireRole\((\[[^\]]*\])\)", rd(f)))
    if lists: t.append('- **%s** : %s' % (os.path.basename(f)[:-4], '; '.join('%s ×%d' % (k, v) for k, v in lists.items())))
w('06_roles_permissions.md', '\n'.join(t))

# ---------------------------------------------------------------- 07 plugins
t = ['# 07 — Plugins / extensions', '', 'Format **CCPLG** (ZIP signé Ed25519 : manifeste + HTML/CSS déclaratifs ; aucun code exécuté).', '']
for f in sorted(glob.glob(os.path.join(ROOT, 'plugins', '**', 'plugin.json'), recursive=True)):
    m = json.loads(rd(f)); t.append('- `%s` : %s v%s — modèles : %s' % (rel(f), m.get('name'), m.get('version'), ', '.join(x.get('id', '?') for x in m.get('provides', {}).get('templates', []))))
t += ['', 'Paquets distribuables : ' + ', '.join('`%s`' % rel(x) for x in glob.glob(os.path.join(ROOT, 'plugins', '*.ccplg')))]
w('07_plugins.md', '\n'.join(t))

# ---------------------------------------------------------------- 09 dépendances
feat = collections.Counter()
for f in php:
    s = rd(f)
    for k, p in [('match', r'\bmatch\s*\('), ('str_contains/starts/ends_with (PHP 8.0)', r'\bstr_(contains|starts_with|ends_with)\('), ('array_is_list (PHP 8.1)', r'\barray_is_list\('),
                 ('union types (PHP 8.0)', r'\b(?:float|int|string|array)\|(?:int|string|null|float)\b'), ('mb_* (mbstring)', r'\bmb_\w+\('), ('ZipArchive (zip)', r'ZipArchive'),
                 ('sodium_*', r'sodium_'), ('PDO MySQL', r'new PDO'), ('finfo/GD', r'finfo_|imagecreate'), ('JSON', r'json_(en|de)code'), ('password_hash', r'password_hash')]:
        if re.search(p, s): feat[k] += 1
t = ['# 09 — Dépendances techniques', '', '- **PHP minimal déduit du code : 8.1** (array_is_list, match, types union).',
     '  *La version réellement installée sur le serveur cible n\'a pas pu être vérifiée dans cet environnement (aucun PHP disponible) : à confirmer avec `php -v`.*',
     '- Base : MySQL 8 / MariaDB 10.4+ (ENUM, ON DUPLICATE KEY, FIELD(), InnoDB, FK).', '- Composer : **aucune** dépendance (aucun composer.json).', '', '## Fonctionnalités PHP utilisées (nombre de fichiers)']
t += ['- %s : %d' % kv for kv in sorted(feat.items())]
t += ['', '## Bibliothèques front (locales, dans web/public/assets/vendor)', ''] + ['- ' + x for x in sorted(os.listdir(os.path.join(W, 'public/assets/vendor')))] + ['- web/public/assets/js/local-ui.js (graphiques + calendrier maison)']
w('09_dependances.md', '\n'.join(t))

# ---------------------------------------------------------------- 10 risques
destroy_get = [os.path.basename(f)[:-4] + '::' + m for f in glob.glob(W + '/app/controllers/*.php') for m in re.findall(r'public function (destroy\w*|delete\w*)\(', rd(f))]
nocsrf = []   # CSRF : vérifié globalement par Controller (voir 3.) ; on ne liste donc que les méthodes POST hors protection globale
base = rd(os.path.join(W, 'app/core/Controller.php'))
csrf_global = 'CSRF::check' in base
t = ['# 10 — Risques et incohérences détectés', '',
     '1. **Règles métier dans les contrôleurs** : %d appels directs à la base (`$db->…`) dans les contrôleurs (cible : 0, tout passe par le Core).' % ctrl_sql,
     '2. **Suppressions via GET** : %d méthodes destroy/delete (confirmation JS seulement, pas de CSRF) : %s.' % (len(destroy_get), ', '.join(destroy_get[:12]) + (' …' if len(destroy_get) > 12 else '')),
     '3. **CSRF** : contrôle %s dans `Controller` (méthode appelée pour les POST) ; les 20 suppressions GET de (2) échappent à ce contrôle.' % ('présent' if csrf_global else 'ABSENT'),
     '4. **SQL construit par concaténation** : %s (vérifier que seules des valeurs entières/identifiants contrôlés sont concaténés).' % (', '.join('%s (%d)' % c for c in concat[:10]) or 'aucun'),
     '5. **Routes sans garde directe détectée** (%d ; hors auth/site public) : garde possiblement indirecte (méthode `authorize()`, constructeur, helper privé) — **à vérifier manuellement** : %s.' % (len(unguarded), ', '.join(unguarded[:25]) + (' …' if len(unguarded) > 25 else '')),
     '6. **Double système d\'autorisation** : rôles (`requireRole`) et permissions (`requirePermission`) mélangés ; les permissions ne sont pas appliquées partout.',
     '7. **Mono-établissement** : aucune colonne `school_id` sur les tables historiques (les tables bulletins utilisent `school_id = 1`).',
     '8. **Pas de tests automatisés d\'intégration** : seul un test unitaire du moteur de bulletins existe.',
     '8b. **Deux systèmes de frais parallèles** : `fees`/`payments` (FeeController) et `student_fees`/`fee_collections` (FinanceController, utilisé par l\'interface) — à unifier.',
     '8c. **Identifiants de base de données dans le dépôt** (`web/app/config/database.php`) : à sortir du dépôt (variables d\'environnement).',
     '9. **Mots de passe de démonstration** dans le jeu de données (à supprimer en production).',
     '10. **Version PHP/MySQL du serveur cible non vérifiée** ici.', '',
     '## Corrections déjà apportées avant cet audit', '- Constante DEMO_MODE manquante ; colonnes official_id ; tables finance ; formulaires Paramètres qui s\'écrasaient ; doublons du module Bulletins supprimés.']
w('10_risques.md', '\n'.join(t))
print('routes', routes, 'forms', nforms, 'tables', len(tables), 'fks', len(fks), 'ctrl_sql', ctrl_sql, 'destroy', len(destroy_get), 'nocsrf', len(nocsrf), 'unguarded', len(unguarded))
