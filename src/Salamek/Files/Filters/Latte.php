<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files\Filters;

use Salamek\Files\FileIconPipe;
use Salamek\Files\ImagePipe;
use Salamek\Files\Models\IFile;
use Salamek\Files\Models\IStructure;
use Salamek\Files\Models\IStructureFile;

/**
 * Class Icon
 */
class Latte
{
    /** @var ImagePipe */
    private $imagePipe;

    /** @var FileIconPipe */
    private $fileIconPipe;

    /**
     * Latte constructor.
     * @param ImagePipe $imagePipe
     * @param FileIconPipe $fileIconPipe
     */
    public function __construct(ImagePipe $imagePipe, FileIconPipe $fileIconPipe, private \Salamek\Files\Access\FileUrl $urls, private \Salamek\Files\Access\BodyFiles $bodyFiles)
    {
        $this->imagePipe = $imagePipe;
        $this->fileIconPipe = $fileIconPipe;
    }

    /**
     * @param null $file
     * @param string|null $size
     * @param string|null $flags
     * @return string
     */
    public function imageRequest($file = null, ?string $size = null, ?string $flags = null): string
    {
        return $this->imagePipe->request($file, $size, $flags);
    }

    public function fileContent(?string $html, string $provider, int|string $reference): string
    {
        return $this->bodyFiles->rewrite($html ?? '', $provider, $reference);
    }

    public function fileRequest(\Salamek\Files\Access\FileReference $file, string $variant = 'original'): string
    {
        return $this->urls->create($file, $variant);
    }

    /**
     * @param IFile|string|null $file
     * @param string|null $size
     * @return string
     */
    public function fileIconRequest($iconName = null, ?string $size = null): string
    {
        if ($iconName instanceof IFile) {
            $iconName = $iconName->getExtension();
        }
        return $this->fileIconPipe->request($iconName, $size);
    }

}
