<?php
declare(strict_types=1);

namespace CocoCore\Plugins\CCPLG;

final class PackageInstaller
{
    public function __construct(
        private PackageReader $reader,
        private ManifestValidator $validator
    ) {
    }

    /** Installs only verified declarative resources. */
    public function install(string $packagePath, string $destinationRoot, string $trustedPublicKeyBase64): array
    {
        $package = $this->reader->read($packagePath);
        $manifest = $this->validator->validate(
            $package['manifest'],
            $package['entries'],
            $package['signature'],
            $trustedPublicKeyBase64
        );

        $target = rtrim($destinationRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $manifest['plugin_id'];
        if (!is_dir($destinationRoot) && !mkdir($destinationRoot, 0750, true) && !is_dir($destinationRoot)) {
            throw new PackageException('Impossible de créer le répertoire des extensions.');
        }
        $temp = $target . '.tmp-' . bin2hex(random_bytes(5));
        if (!mkdir($temp, 0750, true)) {
            throw new PackageException('Impossible de préparer l’installation.');
        }
        try {
            foreach ($package['entries'] as $relative => $content) {
                $relative = str_replace('\\', '/', $relative);
                $destination = $temp . DIRECTORY_SEPARATOR . $relative;
                $parent = dirname($destination);
                if (!is_dir($parent) && !mkdir($parent, 0750, true) && !is_dir($parent)) {
                    throw new PackageException('Impossible de créer un sous-répertoire.');
                }
                if (file_put_contents($destination, $content, LOCK_EX) === false) {
                    throw new PackageException('Échec d’écriture d’une ressource.');
                }
            }
            if (is_dir($target)) {
                throw new PackageException('Cette extension est déjà installée; utiliser le processus de mise à jour.');
            }
            if (!rename($temp, $target)) {
                throw new PackageException('Installation atomique impossible.');
            }
        } catch (\Throwable $e) {
            $this->removeTree($temp);
            throw $e;
        }
        return ['plugin_id' => $manifest['plugin_id'], 'version' => $manifest['version'], 'path' => $target];
    }

    private function removeTree(string $path): void
    {
        if (!is_dir($path) || is_link($path)) {
            return;
        }
        foreach (new \DirectoryIterator($path) as $item) {
            if ($item->isDot()) {
                continue;
            }
            $child = $item->getPathname();
            if ($item->isDir() && !$item->isLink()) {
                $this->removeTree($child);
            } else {
                unlink($child);
            }
        }
        rmdir($path);
    }
}
