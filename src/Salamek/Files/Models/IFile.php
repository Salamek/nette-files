<?php
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
     * @param $sum
     */
    public function setSum($sum);

    /**
     * @param $size
     */
    public function setSize($size);

    /**
     * @param $extension
     */
    public function setExtension($extension);

    /**
     * @param $mimeType
     */
    public function setMimeType($mimeType);

    /**
     * @param string $type
     */
    public function setType($type = self::TYPE_BINARY);

    /**
     * @return string
     */
    public function getSum();

    /**
     * @return int
     */
    public function getSize();

    /**
     * @return string
     */
    public function getExtension();

    /**
     * @return string
     */
    public function getMimeType();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return boolean
     */
    public function isExists();

    /**
     * @return mixed
     */
    public function getBasename();

}