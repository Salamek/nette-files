<?php declare(strict_types=1);
namespace Salamek\Files;
use Salamek\Files\Access\{FileReference, FileUrl, VariantRegistry};

/** URL generation only. Image transformations happen after authorization at delivery. */
class ImagePipe
{
    public function __construct(private FileUrl $urls, private VariantRegistry $variants) {}

    public function request(?FileReference $file = null, ?string $size = null, ?string $flags = null): string
    {
        if ($file !== null) { return $this->urls->create($file, $this->variants->forImage($size, $flags)); }
        // A generic placeholder contains no uploaded/user data and needs no endpoint.
        $width = 100; $height = 100;
        if ($size !== null) {
            $recipe = $this->variants->get($this->variants->forImage($size, $flags));
            $width = $recipe[0] ?? $recipe[1]; $height = $recipe[1] ?? $recipe[0];
        }
        return 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '"><rect width="100%" height="100%" fill="#e9ecef"/></svg>');
    }
}
