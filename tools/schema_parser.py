# -*- coding: utf-8 -*-
"""Lit les migrations SQL et reconstitue le schéma (tables, colonnes, clés) — partagé par check_schema.py et generate_entities.py."""
import glob, os, re
R = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

def read_all():
    files = sorted(glob.glob(R + '/database/migrations/*.sql'))
    return [(f, open(f, encoding='utf-8').read()) for f in files]

def split_top(body):
    out, cur, d, q = [], [], 0, False
    i = 0
    while i < len(body):
        c = body[i]
        if q:
            cur.append(c)
            if c == "'":
                if body[i+1:i+2] == "'": cur.append("'"); i += 1
                else: q = False
            elif c == '\\': cur.append(body[i+1]); i += 1
        else:
            if c == "'": q = True; cur.append(c)
            elif c == '(': d += 1; cur.append(c)
            elif c == ')': d -= 1; cur.append(c)
            elif c == ',' and d == 0: out.append(''.join(cur).strip()); cur = []
            else: cur.append(c)
        i += 1
    if ''.join(cur).strip(): out.append(''.join(cur).strip())
    return out

def parse_column(defn):
    m = re.match(r'`?(\w+)`?\s+([A-Za-z]+)(?:\(([^)]*)\))?(.*)$', defn, re.S)
    if not m: return None
    name, typ, arg, rest = m.group(1), m.group(2).upper(), m.group(3), m.group(4)
    enum = None
    if typ in ('ENUM', 'SET'):
        enum = re.findall(r"'((?:[^']|'')*)'", arg or '')
        enum = [e.replace("''", "'") for e in enum]
    up = rest.upper()
    return {'name': name, 'type': typ, 'arg': arg, 'enum': enum, 'not_null': 'NOT NULL' in up, 'auto': 'AUTO_INCREMENT' in up,
            'default': 'DEFAULT' in up, 'unsigned': 'UNSIGNED' in rest.upper() or typ in ('TIMESTAMP',)}

def build():
    tables = {}; fks = []; keys = {}
    for f, sql in read_all():
        for m in re.finditer(r'CREATE TABLE (?:IF NOT EXISTS )?`?(\w+)`? \((.*?)\n\) ?ENGINE', sql, re.S):
            name, body = m.group(1), m.group(2)
            cols = {}; order = []; pk = None
            for part in split_top(body):
                p = part.strip()
                up = p.upper()
                if up.startswith('PRIMARY KEY'):
                    pk = re.findall(r'`(\w+)`', p)
                elif up.startswith(('UNIQUE', 'KEY', 'INDEX')):
                    keys.setdefault(name, []).append(re.findall(r'`(\w+)`', p.split('(', 1)[1]) if '(' in p else [])
                elif up.startswith('CONSTRAINT') or up.startswith('FOREIGN KEY'):
                    fm = re.search(r'FOREIGN KEY \(`?(\w+)`?\) REFERENCES `?(\w+)`? \(`?(\w+)`?\)', p)
                    if fm: fks.append((name, fm.group(1), fm.group(2), fm.group(3), f))
                else:
                    c = parse_column(p)
                    if c: cols[c['name']] = c; order.append(c['name'])
            tables[name] = {'cols': cols, 'order': order, 'pk': pk or ([c for c in order if cols[c]['auto']][:1])}
        # ALTER TABLE x ADD COLUMN ... (directs ou dans des chaînes PREPARE)
        for m in re.finditer(r"ALTER TABLE `?(\w+)`?\s+((?:ADD COLUMN[^;']*?(?:,\s*)?)+?)(?=;|'|\s*,\s*ADD KEY)", sql, re.S):
            t = m.group(1)
            for am in re.finditer(r'ADD COLUMN `?(\w+)`? ([^,;\']*?)(?=\s+AFTER|,\s*ADD|,\s*$|$|;)', m.group(2) + ' ', re.S):
                if t in tables:
                    c = parse_column(am.group(1) + ' ' + am.group(2))
                    if c and c['name'] not in tables[t]['cols']:
                        tables[t]['cols'][c['name']] = c; tables[t]['order'].append(c['name'])
    return tables, fks

if __name__ == '__main__':
    t, f = build(); print(len(t), 'tables,', len(f), 'FK')
