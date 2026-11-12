<?php
/**
 * Lit une archive .ccplg (ZIP) en mémoire : manifeste, signature et fichiers.
 * Rien n'est extrait sur le disque ici et aucun code n'est exécuté.
 * (Format repris du projet Coco School.)
 */
class CcplgReader
{
    /** @return array{manifest:string,signature:string,entries:array<string,string>} */
    public function read(string $packagePath): array
    {
        if (!class_exists('ZipArchive')) {
            throw new CcplgException('The PHP zip extension is required.');
        }
        $zip = new ZipArchive();
        if ($zip->open($packagePath) !== true) {
            throw new CcplgException('Unreadable CCPLG archive.');
        }
        try {
            if ($zip->numFiles > 202) {
                throw new CcplgException('Too many files in the package.');
            }
            $manifest  = $zip->getFromName('plugin.json');
            $signature = $zip->getFromName('signature.ccsig');
            if (!is_string($manifest) || !is_string($signature)) {
                throw new CcplgException('plugin.json or signature.ccsig is missing.');
            }
            $entries = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (!$stat || str_ends_with($stat['name'], '/')) {
                    continue;
                }
                $name = $stat['name'];
                if ($name === 'plugin.json' || $name === 'signature.ccsig') {
                    continue;
                }
                if (($stat['size'] ?? 0) > 5000000) {
                    throw new CcplgException('Entry too large.');
                }
                $content = $zip->getFromIndex($i);
                if (!is_string($content)) {
                    throw new CcplgException('Cannot read: ' . $name);
                }
                $entries[$name] = $content;
            }
            return ['manifest' => $manifest, 'signature' => trim($signature), 'entries' => $entries];
        } finally {
            $zip->close();
        }
    }
}
