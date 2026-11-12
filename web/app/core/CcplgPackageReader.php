<?php
/**
 * Lecture sûre d'une archive .ccplg (ZIP) : aucune extraction sur disque ici.
 */

declare(strict_types=1);

final class CcplgPackageReader
{
    /** @return array{manifest:string,signature:string,entries:array<string,string>} */
    public function read(string $packagePath): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new CcplgException("L'extension PHP zip est requise.");
        }
        $zip = new ZipArchive();
        if ($zip->open($packagePath) !== true) {
            throw new CcplgException('Archive CCPLG illisible.');
        }
        try {
            if ($zip->numFiles > 202) {
                throw new CcplgException('Trop de fichiers dans le paquet.');
            }
            $manifest  = $zip->getFromName('plugin.json');
            $signature = $zip->getFromName('signature.ccsig');
            if (!is_string($manifest) || !is_string($signature)) {
                throw new CcplgException('plugin.json ou signature.ccsig absent.');
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
                    throw new CcplgException('Entrée trop volumineuse.');
                }
                $content = $zip->getFromIndex($i);
                if (!is_string($content)) {
                    throw new CcplgException('Impossible de lire : ' . $name);
                }
                $entries[$name] = $content;
            }
            return ['manifest' => $manifest, 'signature' => trim($signature), 'entries' => $entries];
        } finally {
            $zip->close();
        }
    }
}
