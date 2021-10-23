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
    public function __construct(ImagePipe $imagePipe, FileIconPipe $fileIconPipe)
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
    public function imageRequest($file = null, string $size = null, string $flags = null): string
    {
        if ($file instanceof IStructureFile) {
            user_error('Passing IStructureFile into imgPipe is deprecated, pass IFile only', E_USER_DEPRECATED);
            $file = $file->getFile();
        }

        return $this->imagePipe->request($file, $size, $flags);
    }

    /**
     * @param IFile|string|null $file
     * @param string|null $size
     * @return string
     */
    public function fileIconRequest($iconName = null, string $size = null): string
    {
        if ($iconName instanceof IFile) {
            $iconName = $iconName->getExtension();
        }
        return $this->fileIconPipe->request($iconName, $size);
    }

}