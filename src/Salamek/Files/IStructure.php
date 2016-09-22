<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files;


interface IStructure
{
    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @param IStructure|null $parent
     * @return $this
     */
    public function setParent(IStructure $parent = null);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return IStructureFile[]
     */
    public function getStructureFiles();

    /**
     * @return IStructure
     */
    public function getParent();

    /**
     * @return IStructure[]
     */
    public function getChildren();
}