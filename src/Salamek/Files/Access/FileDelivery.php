<?php declare(strict_types=1);
namespace Salamek\Files\Access;
use Nette\Application\BadRequestException;
use Salamek\Files\{FileStorage, Tools};

final class FileDelivery
{
    public function __construct(private ProviderRegistry $providers, private VariantRegistry $variants, private FileStorage $storage,
        private int $publicMaxAge = 3600, private array $accelMapping = []) {}

    public function createResponse(string $provider, string $reference, string $sum, string $variant): FileDeliveryResponse
    {
        if (!preg_match('/^[a-z][a-z0-9]*(?:[.-][a-z0-9]+)*$/D', $provider)
            || !preg_match('/^[a-zA-Z0-9_-]{1,180}$/D', $reference)
            || !preg_match('/^(?:[a-f0-9]{32}|[a-f0-9]{64})$/D', $sum) || !$this->variants->has($variant)) { throw new BadRequestException(); }
        $access = $this->providers->resolve($provider, $reference);
        if (!$access || !hash_equals($access->file->getSum(), $sum)
            || ($access->variants !== null && !in_array($variant, $access->variants, true))) { throw new BadRequestException(); }
        $file = $access->file;
        if (!preg_match('/^[a-zA-Z0-9]{1,16}$/D', $file->getExtension())) { throw new BadRequestException(); }
        $root = realpath($this->storage->getDataDir());
        $path = realpath($this->storage->getFileSystemPath($file));
        if (!$root || !$path || !str_starts_with($path, $root . DIRECTORY_SEPARATOR) || !is_file($path)) { throw new BadRequestException(); }
        $mime = preg_match('~^[a-zA-Z0-9.+-]+/[a-zA-Z0-9.+-]+$~D', $file->getMimeType()) ? $file->getMimeType() : 'application/octet-stream';
        $version = $variant;
        if (!in_array($variant, ['original', 'download'], true)) {
            if ($file->getType() !== 'image' || !str_starts_with($mime, 'image/')) { throw new BadRequestException(); }
            $recipe = $this->variants->get($variant);
            $version .= '-' . hash('sha256', json_encode($recipe, JSON_THROW_ON_ERROR));
            // SVG stays vector; its response is sandboxed and cannot load scripts or external resources.
            if ($mime !== 'image/svg+xml') {
                $cacheRoot = realpath($this->storage->getWebTempDir());
                if (!$cacheRoot || is_link($cacheRoot . '/' . substr($sum, 0, 2))) { throw new \RuntimeException('Unsafe image cache.'); }
                $directory = $cacheRoot . '/' . substr($sum, 0, 2);
                if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) { throw new \RuntimeException('Cannot create image cache.'); }
                $jpeg = $mime === 'image/jpeg';
                $generated = $directory . '/' . $sum . '-' . $version . ($jpeg ? '.jpg' : '.png');
                if (is_link($generated)) { throw new \RuntimeException('Unsafe image cache file.'); }
                $lock = fopen($generated . '.lock', 'c');
                if (!$lock || !flock($lock, LOCK_EX)) { throw new \RuntimeException('Cannot lock image cache.'); }
                try {
                    if (!is_file($generated)) {
                        $dimensions = @getimagesize($path);
                        if (!$dimensions || $dimensions[0] * $dimensions[1] > 40_000_000) { throw new BadRequestException('Unsupported image dimensions.'); }
                        $temporary = tempnam($directory, '.image-');
                        try {
                            Tools::resizeImage($path, ...$recipe)->save($temporary, 85, $jpeg ? \Nette\Utils\Image::JPEG : \Nette\Utils\Image::PNG);
                            if (!rename($temporary, $generated)) { throw new \RuntimeException('Cannot save image cache.'); }
                        } finally { if (is_file($temporary)) { unlink($temporary); } }
                    }
                } finally { flock($lock, LOCK_UN); fclose($lock); }
                $path = $generated; $mime = $jpeg ? 'image/jpeg' : 'image/png';
            }
        }
        $filename = preg_replace('~[\x00-\x1f\x7f"\\\\/]~', '_', $access->filename ?? $file->getBasename());
        $inline = in_array($mime, ['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/avif', 'image/bmp', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml', 'application/pdf'], true);
        $accel = null;
        foreach ($this->accelMapping as $directory => $uri) {
            $directory = realpath($directory);
            if ($directory && str_starts_with($path, $directory . DIRECTORY_SEPARATOR)) {
                $accel = rtrim($uri, '/') . '/' . implode('/', array_map('rawurlencode', explode(DIRECTORY_SEPARATOR, substr($path, strlen($directory) + 1))));
                break;
            }
        }
        return new FileDeliveryResponse($path, $filename, $mime, $variant === 'download' || !$inline,
            $access->public, $this->publicMaxAge, hash('sha256', $provider . '/' . $reference . '/' . $sum . '/' . $version), $accel);
    }
}
