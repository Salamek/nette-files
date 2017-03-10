<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files\Models;

/**
 * Interface IStructureRepository
 * @package Salamek\Files\Models
 */
interface IStructureRepository
{
    /**
     * @param $id
     * @return mixed|null|IStructure
     */
    public function getOneById($id);

    /**
     * @param $id
     * @return IStructure[]
     */
    public function getById($id);

    /**
     * @param IStructure|null $structure
     * @return IStructure[]
     */
    public function getByParent(IStructure $structure = null);

    /**
     * @param $name
     * @param IStructure|null $parentStructure
     * @param IStructure|null $ignoreStructure
     * @return boolean
     */
    public function isNameFree($name, IStructure $parentStructure = null, IStructure $ignoreStructure = null);

    /**
     * @param IStructure $child
     * @param IStructure $parent
     */
    public function persistAsLastChildOf(IStructure $child, IStructure $parent);

    /**
     * @param IStructure $structure
     * @return mixed
     */
    public function deleteStructure(IStructure $structure);
}