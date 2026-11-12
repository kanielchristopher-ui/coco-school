#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Auto-test de l'émission/vérification des licences (algorithme identique à CocoCore\\Licensing\\LicenseVerifier).
   Usage : python3 tools/test_license_tool.py"""
import base64, datetime as dt, json, os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import license_tool as L
from cryptography.hazmat.primitives import serialization
from cryptography.hazmat.primitives.asymmetric.ed25519 import Ed25519PrivateKey

k = Ed25519PrivateKey.generate(); other = Ed25519PrivateKey.generate()
pk = base64.b64encode(k.public_key().public_bytes(serialization.Encoding.Raw, serialization.PublicFormat.Raw)).decode()
base = {'v': 1, 'license_id': 'L1', 'holder': 'Test', 'issued_at': '2026-01-01', 'valid_from': '2026-01-01', 'valid_until': '2026-12-31', 'grace_days': 30,
        'edition': 'standard', 'levels': ['primaire'], 'modules': ['*'], 'max_students': {'primaire': 100}}
t = L.issue(base, k); D = dt.date.fromisoformat
cases = [('valid', t, '2026-06-01', None, ()), ('expiring_soon', t, '2026-12-10', None, ()), ('expiring_soon', t, '2026-12-31', None, ()), ('grace', t, '2027-01-15', None, ()),
         ('grace', t, '2027-01-30', None, ()), ('expired', t, '2027-01-31', None, ()), ('not_yet_valid', t, '2025-12-31', None, ()), ('tampered', t, '2026-05-01', '2026-06-01', ()),
         ('valid', t, '2026-05-31', '2026-06-01', ()), ('invalid_signature', L.issue(base, other), '2026-06-01', None, ()), ('malformed', 'CCL1.abc', '2026-06-01', None, ()),
         ('malformed', 'nimporte', '2026-06-01', None, ()), ('revoked', t, '2026-06-01', None, ('L1',))]
bad = 0
for exp, tok, now, last, rev in cases:
    got = L.verify(tok, [pk], D(now), D(last) if last else None, rev)[0]
    bad += got != exp; print(('ok   ' if got == exp else 'FAIL ') + exp, '->', got, now)
j, pl, sig = L.decode(t); pl['valid_until'] = '2099-12-31'
forged = 'CCL1.%s.%s' % (L.b64u(json.dumps(pl, separators=(',', ':')).encode()), t.split('.')[2])
got = L.verify(forged, [pk], D('2026-06-01'))[0]; bad += got != 'invalid_signature'
print(('ok   ' if got == 'invalid_signature' else 'FAIL ') + 'date de fin falsifiée ->', got)
print('ÉCHECS :', bad); sys.exit(1 if bad else 0)
