<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files;


interface IFileRepository
{
    /**
     * @param $id
     * @return null|IFile
     */
    public function getOneById($id);

    /**
     * @param $sum
     * @return null|IFile
     */
    public function getOneBySum($sum);

    /**
     * @param $id
     * @return IFile[]
     */
    public function getById($id);

    /**
     * @param $sum
     * @param IFile|null $fileIgnore
     * @return boolean
     */
    public function isSumFree($sum, IFile $fileIgnore = null);

    /**
     * @param $md5
     * @param $size
     * @param $extension
     * @param $mimeType
     * @param string $type
     * @return IFile
     */
    public function createNewFile($md5, $size, $extension, $mimeType, $type = IFile::TYPE_BINARY);

    /**
     * @param IFile $file
     * @return mixed
     */
    public function deleteFile(IFile $file);

}