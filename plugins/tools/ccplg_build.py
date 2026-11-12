#!/usr/bin/env python3
"""
Construit et SIGNE un paquet .ccplg (extension de bulletins).

Usage :
    python3 tools/ccplg_build.py <dossier_source> <sortie.ccplg> <cle_privee_base64>
    python3 tools/ccplg_build.py --keygen            # génère une paire de clés Ed25519

Le dossier source contient : plugin.json (sans "files"), templates/*.html, templates/*.css, ...
Le script calcule le SHA-256 de chaque fichier, écrit "files" dans le manifeste et signe les octets
exacts du manifeste (signature détachée signature.ccsig, Ed25519, base64).
La clé PUBLIQUE correspondante se place dans app/config/ccplg.php (jamais dans le paquet).
Dépendance : pip install cryptography
"""
import base64, hashlib, json, os, sys, zipfile
from cryptography.hazmat.primitives.asymmetric.ed25519 import Ed25519PrivateKey
from cryptography.hazmat.primitives import serialization as ser

def keygen():
    k = Ed25519PrivateKey.generate()
    priv = k.private_bytes(ser.Encoding.Raw, ser.PrivateFormat.Raw, ser.NoEncryption())
    pub = k.public_key().public_bytes(ser.Encoding.Raw, ser.PublicFormat.Raw)
    print('PRIVEE (secret, à garder hors du dépôt) :', base64.b64encode(priv).decode())
    print('PUBLIQUE (à mettre dans app/config/ccplg.php) :', base64.b64encode(pub).decode())

def build(src, out, priv_b64):
    manifest = json.load(open(os.path.join(src, 'plugin.json'), encoding='utf-8'))
    manifest.update({'format': 'CCPLG', 'application': 'Coco School', 'format_version': 1})
    files, entries = {}, {}
    for root, _, names in os.walk(src):
        for n in sorted(names):
            full = os.path.join(root, n)
            rel = os.path.relpath(full, src).replace(os.sep, '/')
            if rel in ('plugin.json', 'signature.ccsig'):
                continue
            data = open(full, 'rb').read()
            entries[rel] = data
            files[rel] = {'sha256': hashlib.sha256(data).hexdigest()}
    manifest['files'] = dict(sorted(files.items()))
    manifest_bytes = json.dumps(manifest, ensure_ascii=False, indent=2, sort_keys=True).encode('utf-8')
    key = Ed25519PrivateKey.from_private_bytes(base64.b64decode(priv_b64))
    sig = base64.b64encode(key.sign(manifest_bytes))
    with zipfile.ZipFile(out, 'w', zipfile.ZIP_DEFLATED) as z:
        z.writestr('plugin.json', manifest_bytes)
        z.writestr('signature.ccsig', sig)
        for rel, data in entries.items():
            z.writestr(rel, data)
    print('Paquet écrit :', out, '(%d fichiers)' % len(entries))

if __name__ == '__main__':
    if len(sys.argv) == 2 and sys.argv[1] == '--keygen':
        keygen()
    elif len(sys.argv) == 4:
        build(*sys.argv[1:])
    else:
        print(__doc__)
