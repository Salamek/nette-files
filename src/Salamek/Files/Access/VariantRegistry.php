<?php declare(strict_types=1);
namespace Salamek\Files\Access;

/** Fixed recipes prevent arbitrary image dimensions/transformations through HTTP. */
final class VariantRegistry
{
    private const MODES = ['fit', 'fill', 'exact', 'shrink_only', 'stretch', 'crop', 'fit_exact'];
    private array $variants = [];

    public function __construct(array $variants = [])
    {
        foreach ($variants as $name => $recipe) {
            if (!is_string($name) || !preg_match('/^[a-zA-Z0-9_-]{1,80}$/D', $name)
                || in_array($name, ['original', 'download'], true) || !is_array($recipe)
                || array_diff(array_keys($recipe), ['width', 'height', 'resizeMode']) !== []) {
                throw new \InvalidArgumentException('Invalid file variant: ' . $name);
            }
            $width = $recipe['width'] ?? null;
            $height = $recipe['height'] ?? null;
            $mode = array_key_exists('resizeMode', $recipe) ? $recipe['resizeMode'] : 'fit';
            if (($width === null && $height === null) || !in_array($mode, self::MODES, true)) {
                throw new \InvalidArgumentException('Invalid file variant: ' . $name);
            }
            foreach ([$width, $height] as $dimension) {
                if ($dimension !== null && (!is_int($dimension) || $dimension < 1 || $dimension > 4096)) {
                    throw new \InvalidArgumentException('Invalid image dimension.');
                }
            }
            // Keep the internal rendering recipe/cache key stable for unchanged definitions.
            $this->variants[$name] = [$width, $height, $mode === 'fit' ? null : $mode];
        }
    }

    public function has(string $name): bool { return in_array($name, ['original', 'download'], true) || isset($this->variants[$name]); }
    public function get(string $name): array { return $this->variants[$name] ?? throw new \InvalidArgumentException('Unknown image variant.'); }

    public function forImage(?string $size, ?string $flags): string
    {
        if ($size === null && $flags === null) { return 'original'; }
        if (!preg_match('/^(\d*)x(\d*)$/D', (string) $size, $match)) { throw new \InvalidArgumentException('Invalid image size.'); }
        $recipe = [$match[1] !== '' ? (int) $match[1] : null, $match[2] !== '' ? (int) $match[2] : null, $flags === 'fit' ? null : $flags];
        foreach ($this->variants as $name => $candidate) { if ($candidate === $recipe) { return $name; } }
        throw new \InvalidArgumentException('Image recipe is not registered: ' . $size . ' ' . $flags);
    }
}
