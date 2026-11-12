<?php
declare(strict_types=1);

namespace CocoCore\Plugins\CCPLG;

final class PackageReader
{
    /** @return array{manifest:string,signature:string,entries:array<string,string>} */
    public function read(string $packagePath): array
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new PackageException('L’extension PHP zip est requise.');
        }
        $zip = new \ZipArchive();
        if ($zip->open($packagePath) !== true) {
            throw new PackageException('Archive CCPLG illisible.');
        }
        try {
            if ($zip->numFiles > 202) {
                throw new PackageException('Trop de fichiers dans le paquet.');
            }
            $manifest = $zip->getFromName('plugin.json');
            $signature = $zip->getFromName('signature.ccsig');
            if (!is_string($manifest) || !is_string($signature)) {
                throw new PackageException('plugin.json ou signature.ccsig absent.');
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
                if (($stat['size'] ?? 0) > 5_000_000) {
                    throw new PackageException('Entrée trop volumineuse.');
                }
                $content = $zip->getFromIndex($i);
                if (!is_string($content)) {
                    throw new PackageException('Impossible de lire : ' . $name);
                }
                $entries[$name] = $content;
            }
            return ['manifest' => $manifest, 'signature' => trim($signature), 'entries' => $entries];
        } finally {
            $zip->close();
        }
    }
}
