#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Génère core/Domain/Entities/*.php et core/Domain/EntityRegistry.php depuis les migrations.
   Usage : python3 tools/generate_entities.py   (à relancer après chaque nouvelle migration)"""
import os, re, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from schema_parser import build, R

OVERRIDES = {'classes': 'SchoolClass', 'parents': 'Guardian', 'timetables': 'TimetableEntry', 'academic_sessions': 'AcademicYear',
             'licenses': 'LicenseRecord', 'settings': 'Setting', 'attendance': 'Attendance', 'salary': 'Salary', 'staff': 'Staff',
             'system_state': 'SystemState', 'fee_collections': 'FeeCollection', 'student_fees': 'StudentFee'}

def class_name(t):
    if t in OVERRIDES: return OVERRIDES[t]
    parts = t.split('_')
    last = parts[-1]
    if last.endswith('ies'): last = last[:-3] + 'y'
    elif last.endswith('sses'): last = last[:-2]
    elif last.endswith('s') and not last.endswith('ss') and last not in ('status',): last = last[:-1]
    parts[-1] = last
    return ''.join(p.capitalize() for p in parts)

def php_type(c):
    t = c['type']
    if t in ('INT', 'BIGINT', 'SMALLINT', 'MEDIUMINT'): base = 'int'
    elif t == 'TINYINT': base = 'bool' if (c['arg'] or '') == '1' else 'int'
    elif t in ('DECIMAL', 'FLOAT', 'DOUBLE'): base = 'float'
    else: base = 'string'
    nullable = not c['not_null'] and not c['auto']
    return ('?' if nullable else '') + base

tables, _ = build()
outdir = os.path.join(R, 'core/Domain/Entities'); os.makedirs(outdir, exist_ok=True)
for f in os.listdir(outdir):
    if f.endswith('.php'): os.remove(os.path.join(outdir, f))
registry = {}
for t, info in sorted(tables.items()):
    cn = class_name(t); registry[t] = cn
    cols = info['cols']; order = info['order']
    pk = info['pk'] or ['id']
    required = [c for c in order if cols[c]['not_null'] and not cols[c]['auto'] and not cols[c]['default']]
    enums = {c: cols[c]['enum'] for c in order if cols[c]['enum']}
    lengths = {c: int(cols[c]['arg']) for c in order if cols[c]['type'] in ('VARCHAR', 'CHAR') and cols[c]['arg'] and cols[c]['arg'].isdigit()}
    def php_list(l): return '[' + ', '.join("'%s'" % x.replace("'", "\\'") for x in l) + ']'
    lines = ['<?php', '// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.', 'declare(strict_types=1);', '',
             'namespace CocoCore\\Domain\\Entities;', '', 'use CocoCore\\Domain\\Entity;', '', '/** Table `%s`. */' % t, 'final class %s extends Entity' % cn, '{',
             "    public const TABLE = '%s';" % t, '    public const PRIMARY_KEY = %s;' % php_list(pk), '    public const FIELDS = [']
    for c in order: lines.append("        '%s' => '%s'," % (c, php_type(cols[c])))
    lines += ['    ];', '    public const REQUIRED = %s;' % php_list(required)]
    lines.append('    public const ENUMS = [' + ('' if not enums else '\n' + '\n'.join("        '%s' => %s," % (c, php_list(v)) for c, v in enums.items()) + '\n    ') + '];')
    lines.append('    public const LENGTHS = [' + ('' if not lengths else '\n' + '\n'.join("        '%s' => %d," % (c, v) for c, v in lengths.items()) + '\n    ') + '];')
    lines += ['}', '']
    open(os.path.join(outdir, cn + '.php'), 'w', encoding='utf-8').write('\n'.join(lines))
reg = ['<?php', '// GÉNÉRÉ par tools/generate_entities.py — NE PAS MODIFIER À LA MAIN.', 'declare(strict_types=1);', '', 'namespace CocoCore\\Domain;', '',
       '/** Table => classe de modèle : tous les modèles du Core. */', 'final class EntityRegistry', '{', '    public const TABLES = [']
for t, cn in registry.items(): reg.append("        '%s' => Entities\\%s::class," % (t, cn))
reg += ['    ];', '', '    /** @return class-string<Entity> */', '    public static function forTable(string $table): string',
        '    {', "        return self::TABLES[$table] ?? throw new \\InvalidArgumentException('Table inconnue : ' . $table);", '    }', '', '    /** @return list<class-string<Entity>> */',
        '    public static function all(): array', '    {', '        return array_values(self::TABLES);', '    }', '}', '']
open(os.path.join(R, 'core/Domain/EntityRegistry.php'), 'w', encoding='utf-8').write('\n'.join(reg))
print(len(registry), 'modèles générés')
