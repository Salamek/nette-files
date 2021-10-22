<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files\Models;

/**
 * Interface IStructureFile
 * @package Salamek\Files\Models
 */
interface IStructureFile
{
    /**
     * @param string $name
     */
    public function setName(string $name);

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return IFile
     */
    public function getFile(): IFile;

    /**
     * @return IStructure
     */
    public function getStructure(): IStructure;

    /**
     * @return string
     */
    public function getBasename(): string;
}