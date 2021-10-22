<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files\Models;

/**
 * Interface IFileRepository
 * @package Salamek\Files\Models
 */
interface IFileRepository
{
    /**
     * @param int $id
     * @return IFile|null
     */
    public function getOneById(int $id): ?IFile;

    /**
     * @param string $sum
     * @return IFile|null
     */
    public function getOneBySum(string $sum): ?IFile;

    /**
     * @param int|array $id
     * @return IFile[]
     */
    public function getById($id);

    /**
     * @param string $sum
     * @param IFile|null $fileIgnore
     * @return boolean
     */
    public function isSumFree(string $sum, IFile $fileIgnore = null): bool;

    /**
     * @param string $sum
     * @param int $size
     * @param string $extension
     * @param string $mimeType
     * @param string $type
     * @return IFile
     */
    public function createNewFile(string $sum, int $size, string $extension, string $mimeType, string $type = IFile::TYPE_BINARY): IFile;

    /**
     * @param IFile $file
     * @return void
     */
    public function deleteFile(IFile $file): void;

}