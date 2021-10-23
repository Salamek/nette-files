<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files\Filters;

use Salamek\Files\FileStorage;
use Salamek\Files\ImagePipe;
use Salamek\Files\Models\IFile;
use Salamek\Files\Models\IStructureFile;

/**
 * Class Icon
 */
class Latte
{
    /** @var FileStorage */
    private $fileStorage;

    /** @var ImagePipe */
    private $imagePipe;

    /**
     * Latte constructor.
     * @param FileStorage $fileStorage
     * @param ImagePipe $imagePipe
     */
    public function __construct(FileStorage $fileStorage, ImagePipe $imagePipe)
    {
        $this->fileStorage = $fileStorage;
        $this->imagePipe = $imagePipe;
    }

    /**
     * @param IFile $file
     * @return string
     */
    public function fileIcon(IFile $file): string
    {
        return $this->fileStorage->getIcon($file);
    }

    /**
     * @return ImagePipe
     */
    public function getImagePipe(): ImagePipe
    {
        return $this->imagePipe;
    }

    /**
     * @param null $file
     * @param string|null $size
     * @param string|null $flags
     * @return string
     */
    public function request($file = null, string $size = null, string $flags = null): string
    {
        if ($file instanceof IStructureFile) {
            user_error('Passing IStructureFile into imgPipe is deprecated, pass IFile only', E_USER_DEPRECATED);
            $file = $file->getFile();
        }

        return $this->imagePipe->request($file, $size, $flags);
    }

}