<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files;

use Salamek\Files\Models\IFile;

/**
 * Interface IPipe
 * @package Salamek\Files
 */
interface IPipe
{
    /**
     * @param IFile|null $structureFile
     * @return string
     */
    public function request(IFile $structureFile = null): string;
}