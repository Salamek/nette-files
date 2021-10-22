<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files\Models;

/**
 * Interface IFile
 * @package Salamek\Files\Models
 */
interface IFile
{
    const TYPE_IMAGE = 'image';
    const TYPE_TEXT = 'text';
    const TYPE_MEDIA = 'media';
    const TYPE_BINARY = 'binary';

    /**
     * @param string $sum
     */
    public function setSum(string $sum): void;

    /**
     * @param int $size
     */
    public function setSize(int $size): void;

    /**
     * @param string $extension
     */
    public function setExtension(string $extension): void;

    /**
     * @param string $mimeType
     */
    public function setMimeType(string $mimeType): void;

    /**
     * @param string $type
     */
    public function setType(string $type = self::TYPE_BINARY): void;

    /**
     * @return string
     */
    public function getSum(): string;

    /**
     * @return int
     */
    public function getSize(): int;

    /**
     * @return string
     */
    public function getExtension(): string;

    /**
     * @return string
     */
    public function getMimeType(): string;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return boolean
     */
    public function isExists(): bool;

    /**
     * @return string
     */
    public function getBasename(): string;

}