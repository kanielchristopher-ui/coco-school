#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Émission et vérification des licences COCO SCHOOL (format CCL1, signature Ed25519 — compatible avec le Core PHP/libsodium).

  python3 tools/license_tool.py keygen --out ./cles               crée private.key (SECRÈTE) et public.key
  python3 tools/license_tool.py issue --private ./cles/private.key --holder "Complexe X" \\
        --until 2027-08-31 --levels maternel,primaire,secondaire --students maternel=100,primaire=500,secondaire=800 [--from 2026-09-01] [--grace 30]
  python3 tools/license_tool.py inspect <clé>                     affiche le contenu
  python3 tools/license_tool.py verify <clé> --public <base64|fichier> [--now 2027-01-01] [--last-seen 2027-01-02]

LA CLÉ PRIVÉE NE DOIT JAMAIS ÊTRE SUR LE SERVEUR DU CLIENT : seul l'éditeur émet des licences.
"""
import argparse, base64, datetime as dt, json, os, sys, uuid
from cryptography.hazmat.primitives import serialization
from cryptography.hazmat.primitives.asymmetric.ed25519 import Ed25519PrivateKey, Ed25519PublicKey
from cryptography.exceptions import InvalidSignature

PREFIX = 'CCL1'
b64u = lambda b: base64.urlsafe_b64encode(b).rstrip(b'=').decode()
def unb64u(s):
    return base64.urlsafe_b64decode(s + '=' * (-len(s) % 4))

def load_secret(path):
    raw = base64.b64decode(open(path).read().strip())
    if len(raw) not in (32, 64): raise SystemExit('Clé privée invalide (32 ou 64 octets attendus).')
    return Ed25519PrivateKey.from_private_bytes(raw[:32])

def issue(payload, priv):
    j = json.dumps(payload, ensure_ascii=False, separators=(',', ':')).encode()
    return '%s.%s.%s' % (PREFIX, b64u(j), b64u(priv.sign(j)))

def decode(token):
    p = ''.join(token.split()).split('.')
    if len(p) != 3 or p[0] != PREFIX: raise ValueError('format invalide')
    j = unb64u(p[1]); sig = unb64u(p[2])
    return j, json.loads(j), sig

def verify(token, pubs, now, last_seen=None, revoked=(), expiring=30, tol=1):
    """Même algorithme que CocoCore\\Licensing\\LicenseVerifier. Retourne (statut, jours_restants)."""
    try: j, payload, sig = decode(token)
    except Exception: return 'malformed', None
    if len(sig) != 64: return 'malformed', None
    ok = False
    for pk in pubs:
        try: Ed25519PublicKey.from_public_bytes(base64.b64decode(pk)).verify(sig, j); ok = True; break
        except (InvalidSignature, ValueError): pass
    if not ok: return 'invalid_signature', None
    try:
        vf = dt.date.fromisoformat(payload['valid_from']); vu = dt.date.fromisoformat(payload['valid_until']); grace = int(payload.get('grace_days', 0))
        if vu < vf or not (0 <= grace <= 365): raise ValueError
        for l in payload.get('levels', []):
            if l not in ('maternel', 'primaire', 'secondaire'): raise ValueError
    except Exception: return 'malformed', None
    if payload['license_id'] in revoked: return 'revoked', None
    if last_seen and now < last_seen - dt.timedelta(days=tol): return 'tampered', None
    if now < vf: return 'not_yet_valid', None
    left = (vu - now).days
    if left >= 0: return ('expiring_soon' if left <= expiring else 'valid'), left
    if (now - vu).days <= grace: return 'grace', left
    return 'expired', left

def main():
    ap = argparse.ArgumentParser(); sp = ap.add_subparsers(dest='cmd', required=True)
    k = sp.add_parser('keygen'); k.add_argument('--out', default='.')
    i = sp.add_parser('issue')
    for a in ('private', 'holder', 'until'): i.add_argument('--' + a, required=True)
    i.add_argument('--from', dest='from_', default=dt.date.today().isoformat()); i.add_argument('--grace', type=int, default=30)
    i.add_argument('--levels', default='maternel,primaire,secondaire'); i.add_argument('--students', default='')
    i.add_argument('--modules', default='*'); i.add_argument('--edition', default='standard'); i.add_argument('--id', default='')
    n = sp.add_parser('inspect'); n.add_argument('token')
    v = sp.add_parser('verify'); v.add_argument('token'); v.add_argument('--public', required=True); v.add_argument('--now'); v.add_argument('--last-seen')
    a = ap.parse_args()
    if a.cmd == 'keygen':
        os.makedirs(a.out, exist_ok=True); priv = Ed25519PrivateKey.generate()
        seed = priv.private_bytes(serialization.Encoding.Raw, serialization.PrivateFormat.Raw, serialization.NoEncryption())
        pub = priv.public_key().public_bytes(serialization.Encoding.Raw, serialization.PublicFormat.Raw)
        open(os.path.join(a.out, 'private.key'), 'w').write(base64.b64encode(seed + pub).decode() + '\n'); os.chmod(os.path.join(a.out, 'private.key'), 0o600)
        open(os.path.join(a.out, 'public.key'), 'w').write(base64.b64encode(pub).decode() + '\n')
        print('Clé publique (à mettre dans core/Licensing/trusted_keys.php) :', base64.b64encode(pub).decode())
    elif a.cmd == 'issue':
        payload = {'v': 1, 'license_id': a.id or 'LIC-' + uuid.uuid4().hex[:12].upper(), 'holder': a.holder, 'issued_at': dt.date.today().isoformat(),
                   'valid_from': a.from_, 'valid_until': a.until, 'grace_days': a.grace, 'edition': a.edition,
                   'levels': [x for x in a.levels.split(',') if x], 'modules': a.modules.split(','),
                   'max_students': {kv.split('=')[0]: int(kv.split('=')[1]) for kv in a.students.split(',') if '=' in kv}}
        print(issue(payload, load_secret(a.private)))
    elif a.cmd == 'inspect':
        print(json.dumps(decode(a.token)[1], ensure_ascii=False, indent=2))
    elif a.cmd == 'verify':
        pub = open(a.public).read().strip() if os.path.isfile(a.public) else a.public
        now = dt.date.fromisoformat(a.now) if a.now else dt.date.today()
        print(*verify(a.token, [pub], now, dt.date.fromisoformat(a.last_seen) if a.last_seen else None))
if __name__ == '__main__': main()
