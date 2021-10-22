<?php declare(strict_types = 1);
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
    public function getOneById(int $id): ?IStructure;

    /**
     * @param int|array $id
     * @return IStructure[]
     */
    public function getById($id);

    /**
     * @param IStructure|null $structure
     * @return IStructure[]
     */
    public function getByParent(IStructure $structure = null);

    /**
     * @param string $name
     * @param IStructure|null $parentStructure
     * @param IStructure|null $ignoreStructure
     * @return boolean
     */
    public function isNameFree(string $name, IStructure $parentStructure = null, IStructure $ignoreStructure = null): bool;

    /**
     * @param IStructure $child
     * @param IStructure $parent
     */
    public function persistAsLastChildOf(IStructure $child, IStructure $parent): void;

    /**
     * @param IStructure $structure
     * @return void
     */
    public function deleteStructure(IStructure $structure): void;
}