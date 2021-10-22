<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files\Models;

/**
 * Interface IStructure
 * @package Salamek\Files\Models
 */
interface IStructure
{
    /**
     * @param string $name
     */
    public function setName(string $name): void;

    /**
     * @param IStructure|null $parent
     * @return void
     */
    public function setParent(IStructure $parent = null): void;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return IStructureFile[]
     */
    public function getStructureFiles();

    /**
     * @return IStructure|null
     */
    public function getParent(): ?IStructure;

    /**
     * @return IStructure[]
     */
    public function getChildren();
}