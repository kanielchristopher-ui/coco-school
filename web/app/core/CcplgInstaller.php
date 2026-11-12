<?php
/**
 * Installe un paquet .ccplg vérifié dans storage/extensions/<plugin_id>/<version>/.
 * Seules des ressources déclaratives (JSON, HTML, CSS, images) sont écrites ;
 * installation atomique (répertoire temporaire puis rename).
 */
class CcplgInstaller
{
    private CcplgReader $reader;
    private CcplgValidator $validator;

    public function __construct(?CcplgReader $reader = null, ?CcplgValidator $validator = null)
    {
        $this->reader    = $reader ?? new CcplgReader();
        $this->validator = $validator ?? new CcplgValidator();
    }

    /** @return array{plugin_id:string,version:string,path:string,manifest:array,sha256:string} */
    public function install(string $packagePath, string $destinationRoot, string $trustedPublicKeyBase64): array
    {
        $package  = $this->reader->read($packagePath);
        $manifest = $this->validator->validate($package['manifest'], $package['entries'], $package['signature'], $trustedPublicKeyBase64);

        $version = (string) $manifest['version'];
        if (!preg_match('/^[0-9A-Za-z._-]{1,30}$/', $version)) {
            throw new CcplgException('Invalid version.');
        }
        $pluginDir = rtrim($destinationRoot, '/\\') . DIRECTORY_SEPARATOR . $manifest['plugin_id'];
        $target    = $pluginDir . DIRECTORY_SEPARATOR . $version;
        if (!is_dir($pluginDir) && !mkdir($pluginDir, 0750, true) && !is_dir($pluginDir)) {
            throw new CcplgException('Cannot create the extensions directory.');
        }
        if (is_dir($target)) {
            throw new CcplgException('This extension version is already installed.');
        }
        $temp = $target . '.tmp-' . bin2hex(random_bytes(5));
        if (!mkdir($temp, 0750, true)) {
            throw new CcplgException('Cannot prepare the installation.');
        }
        try {
            foreach ($package['entries'] as $relative => $content) {
                $destination = $temp . DIRECTORY_SEPARATOR . str_replace('\\', '/', $relative);
                $parent = dirname($destination);
                if (!is_dir($parent) && !mkdir($parent, 0750, true) && !is_dir($parent)) {
                    throw new CcplgException('Cannot create a sub-directory.');
                }
                if (file_put_contents($destination, $content, LOCK_EX) === false) {
                    throw new CcplgException('Failed to write a resource.');
                }
            }
            file_put_contents($temp . DIRECTORY_SEPARATOR . 'plugin.json', $package['manifest'], LOCK_EX);
            if (!rename($temp, $target)) {
                throw new CcplgException('Atomic installation impossible.');
            }
        } catch (Throwable $e) {
            $this->removeTree($temp);
            throw $e;
        }
        return [
            'plugin_id' => (string) $manifest['plugin_id'],
            'version'   => $version,
            'path'      => $target,
            'manifest'  => $manifest,
            'sha256'    => hash_file('sha256', $packagePath) ?: '',
        ];
    }

    private function removeTree(string $path): void
    {
        if (!is_dir($path) || is_link($path)) {
            return;
        }
        foreach (new DirectoryIterator($path) as $item) {
            if ($item->isDot()) {
                continue;
            }
            $child = $item->getPathname();
            if ($item->isDir() && !$item->isLink()) {
                $this->removeTree($child);
            } else {
                @unlink($child);
            }
        }
        @rmdir($path);
    }
}
