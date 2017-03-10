<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files\Filters;

use Salamek\Files\FileStorage;
use Salamek\Files\Models\IFile;

/**
 * Class Icon
 */
class Latte
{
    /** @var FileStorage */
    private $fileStorage;

    /**
     * Icon constructor.
     * @param FileStorage $fileStorage
     */
    public function __construct(FileStorage $fileStorage)
    {
        $this->fileStorage = $fileStorage;
    }

    /**
     * @param IFile $file
     * @return string
     */
    public function fileIcon(IFile $file)
    {
        return $this->fileStorage->getIcon($file);
    }
}