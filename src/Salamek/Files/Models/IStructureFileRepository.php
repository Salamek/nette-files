<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files\Models;

/**
 * Interface IStructureFileRepository
 * @package Salamek\Files\Models
 */
interface IStructureFileRepository
{
    /**
     * @param $id
     * @return null|IStructureFile
     */
    public function getOneById(int $id): ?IStructureFile;

    /**
     * @param array|integer $id
     * @return IStructureFile[]
     */
    public function getById($id);

    /**
     * @param IStructure|null $structure
     * @return IStructureFile[]
     */
    public function getByStructure(IStructure $structure = null);

    /**
     * @param string $name
     * @param IStructure $structure
     * @return mixed
     */
    public function getOneByNameAndStructure(string $name, IStructure $structure);

    /**
     * @param $name
     * @param IStructure|null $structure
     * @param IStructureFile|null $structureFileIgnore
     * @return boolean
     */
    public function isNameFree(string $name, IStructure $structure = null, IStructureFile $structureFileIgnore = null): bool;

    /**
     * @param string $insertName
     * @param IFile $newFile
     * @param IStructure|null $structure
     * @return IStructureFile
     */
    public function createNewStructureFile(string $insertName, IFile $newFile, IStructure $structure = null): IStructureFile;

    /**
     * @param IStructureFile $structureFile
     * @return void
     */
    public function deleteStructureFile(IStructureFile $structureFile): void;
}