<?php declare(strict_types=1);
namespace Salamek\Files\Access;

/** Fixed recipes prevent arbitrary image dimensions/transformations through HTTP. */
final class VariantRegistry
{
    private array $variants;

    public function __construct(array $variants = [])
    {
        $this->variants = $variants;
        foreach ($variants as $name => $recipe) {
            if (!preg_match('/^[a-zA-Z0-9_-]{1,80}$/D', $name) || in_array($name, ['original', 'download'], true)
                || count($recipe) !== 3 || !in_array($recipe[2], [null, 'fit', 'fill', 'exact', 'shrink_only', 'stretch', 'crop', 'fit_exact'], true)
                || ($recipe[0] === null && $recipe[1] === null)) { throw new \InvalidArgumentException('Invalid file variant: ' . $name); }
            foreach (array_slice($recipe, 0, 2) as $dimension) {
                if ($dimension !== null && (!is_int($dimension) || $dimension < 1 || $dimension > 4096)) { throw new \InvalidArgumentException('Invalid image dimension.'); }
            }
        }
    }

    public function has(string $name): bool { return in_array($name, ['original', 'download'], true) || isset($this->variants[$name]); }
    public function get(string $name): array { return $this->variants[$name] ?? throw new \InvalidArgumentException('Unknown image variant.'); }

    public function forImage(?string $size, ?string $flags): string
    {
        if ($size === null && $flags === null) { return 'original'; }
        if (!preg_match('/^(\d*)x(\d*)$/D', (string) $size, $match)) { throw new \InvalidArgumentException('Invalid image size.'); }
        $recipe = [$match[1] !== '' ? (int) $match[1] : null, $match[2] !== '' ? (int) $match[2] : null, $flags];
        foreach ($this->variants as $name => $candidate) { if ($candidate === $recipe) { return $name; } }
        throw new \InvalidArgumentException('Image recipe is not registered: ' . $size . ' ' . $flags);
    }
}
