#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Tests statiques du schéma : clés étrangères valides, INSERT cohérents avec les colonnes, colonnes utilisées par le code PHP.
   Usage : python3 tools/check_schema.py   (code de sortie 1 si une erreur)"""
import re, sys, glob, os
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from schema_parser import build, read_all, split_top, R

tables, fks = build()
errors = []
def err(m): errors.append(m)

for t, c, rt, rc, f in fks:
    if rt not in tables: err('FK %s.%s -> table inconnue %s (%s)' % (t, c, rt, os.path.basename(f)))
    elif rc not in tables[rt]['cols']: err('FK %s.%s -> colonne inconnue %s.%s' % (t, c, rt, rc))
    if t in tables and c not in tables[t]['cols']: err('FK sur colonne inexistante %s.%s' % (t, c))

def stmts(sql):
    out, cur, q, i = [], [], False, 0
    while i < len(sql):
        ch = sql[i]; cur.append(ch)
        if q:
            if ch == "'":
                if sql[i+1:i+2] == "'": cur.append("'"); i += 1
                else: q = False
            elif ch == '\\': cur.append(sql[i+1]); i += 1
        else:
            if ch == "'": q = True
            elif sql.startswith('--', i):
                while i < len(sql) and sql[i] != '\n': cur.append(sql[i]); i += 1
                continue
            elif ch == ';': out.append(''.join(cur)); cur = []
        i += 1
    return out

files = [f for f in sorted(glob.glob(R + '/database/migrations/*.sql')) + sorted(glob.glob(R + '/database/seeds/*.sql'))]
nins = 0
for f in files:
    for s in stmts(open(f, encoding='utf-8').read()):
        s = re.sub(r'(?m)^\s*--.*$', '', s).strip()
        m = re.match(r'INSERT(?: IGNORE)? INTO `?(\w+)`?\s*\(([^)]*)\)', s, re.I)
        if m:
            t = m.group(1); cols = [c.strip(' `') for c in m.group(2).split(',')]
            if t not in tables: err('%s : INSERT dans table inconnue %s' % (os.path.basename(f), t)); continue
            for c in cols:
                if c not in tables[t]['cols']: err('%s : INSERT %s colonne inconnue %s' % (os.path.basename(f), t, c))
            nins += 1
        m = re.match(r'UPDATE `?(\w+)`? SET (.*?)(?: WHERE| $|$)', s, re.I | re.S)
        if m and m.group(1) in tables:
            for c in re.findall(r'`(\w+)`\s*=', m.group(2)):
                if c not in tables[m.group(1)]['cols']: err('%s : UPDATE %s colonne inconnue %s' % (os.path.basename(f), m.group(1), c))

# colonnes référencées par les INSERT du code PHP (modèles/contrôleurs) : ->insert('table', [...'col' =>...])
for f in glob.glob(R + '/web/app/**/*.php', recursive=True) + glob.glob(R + '/core/**/*.php', recursive=True):
    s = open(f, encoding='utf-8').read()
    for m in re.finditer(r"->(?:insert|update)\('(\w+)',\s*\[(.*?)\](?:\)|,)", s, re.S):
        t = m.group(1)
        if t in tables:
            for c in re.findall(r"'(\w+)'\s*=>", m.group(2)):
                if c not in tables[t]['cols']: err('%s : insert(%s) colonne inconnue « %s »' % (os.path.relpath(f, R), t, c))
print('%d tables, %d clés étrangères, %d INSERT vérifiés' % (len(tables), len(fks), nins))
for e in errors: print('ERREUR', e)
print('OK' if not errors else '%d erreur(s)' % len(errors))
sys.exit(1 if errors else 0)
