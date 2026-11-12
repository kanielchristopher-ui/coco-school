# Extensions CCPLG

Les paquets de production contiennent `plugin.json`, les fichiers déclarés dans `files` et `signature.ccsig`.
Les deux configurations de référence se trouvent dans `config/evaluations/` pendant le bootstrap. Elles seront empaquetées dans les extensions distribuables une fois la clé de signature officielle provisionnée.

Ne placez jamais de code PHP, JavaScript, SQL ou scripts exécutables dans un paquet. Les modèles HTML/CSS sont des ressources déclaratives et doivent être rendus par le moteur avec échappement des variables.
