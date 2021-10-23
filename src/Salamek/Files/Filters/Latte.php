<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files\Filters;

use Salamek\Files\ImagePipe;
use Salamek\Files\Models\IStructureFile;

/**
 * Class Icon
 */
class Latte
{
    /** @var ImagePipe */
    private $imagePipe;

    /**
     * Latte constructor.
     * @param ImagePipe $imagePipe
     */
    public function __construct(ImagePipe $imagePipe)
    {
        $this->imagePipe = $imagePipe;
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