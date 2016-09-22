<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files;


interface IStructureFileRepository
{
    /**
     * @param $id
     * @return null|IStructureFile
     */
    public function getOneById($id);

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
     * @param $name
     * @param IStructure $structure
     * @return IStructureFile[]
     */
    public function getOneByNameAndStructure($name, IStructure $structure);

    /**
     * @param $name
     * @param IStructure|null $structure
     * @param IStructureFile|null $structureFileIgnore
     * @return boolean
     */
    public function isNameFree($name, IStructure $structure = null, IStructureFile $structureFileIgnore = null);

    /**
     * @param $insertName
     * @param IFile $newFile
     * @param IStructure|null $structure
     * @return IStructureFile
     */
    public function createNewStructureFile($insertName, IFile $newFile, IStructure $structure = null);

    /**
     * @param IStructureFile $structureFile
     * @return mixed
     */
    public function deleteStructureFile(IStructureFile $structureFile);
}