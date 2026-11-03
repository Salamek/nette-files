<?php declare(strict_types=1);
namespace Salamek\Files;

/** Moves old public storage without overwriting destination files or following links. */
final class StorageMigration
{
    public function plan(array $directories, array $keep = []): array
    {
        $plan = [];
        foreach ($directories as $source => $target) {
            if (is_link($source) || is_link($target)) { throw new \RuntimeException('Storage migration refuses symlink roots.'); }
            if (!is_dir($source)) { continue; }
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS)) as $file) {
                if ($file->isLink() || !$file->isFile()) { throw new \RuntimeException('Unexpected storage entry: ' . $file->getPathname()); }
                $relative = substr($file->getPathname(), strlen($source) + 1);
                if (in_array($file->getPathname(), $keep, true) || in_array($relative, ['README.md', '.htaccess', 'web.config'], true)) { continue; }
                $destination = $target . '/' . $relative;
                $parent = dirname($destination);
                while ($parent !== dirname($target)) {
                    if (is_link($parent) || (file_exists($parent) && !is_dir($parent))) { throw new \RuntimeException('Unsafe migration destination: ' . $parent); }
                    $parent = dirname($parent);
                }
                if (file_exists($destination) || is_link($destination)) { throw new \RuntimeException('Storage destination already exists: ' . $destination); }
                $plan[$file->getPathname()] = $destination;
            }
        }
        return $plan;
    }

    public function apply(array $directories, array $keep = []): int
    {
        $plan = $this->plan($directories, $keep); $moved = [];
        try {
            foreach ($plan as $source => $destination) {
                if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0775, true) && !is_dir(dirname($destination))) { throw new \RuntimeException('Cannot create storage destination.'); }
                $hash = hash_file('sha256', $source);
                if (!rename($source, $destination)) { throw new \RuntimeException('Cannot move file: ' . $source); }
                $moved[$source] = $destination;
                if (!hash_equals($hash, hash_file('sha256', $destination))) { throw new \RuntimeException('Storage integrity check failed: ' . $source); }
            }
        } catch (\Throwable $e) {
            foreach (array_reverse($moved, true) as $source => $destination) { rename($destination, $source); }
            throw $e;
        }
        return count($moved);
    }
}
