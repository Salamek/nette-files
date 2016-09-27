<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files\Models;


interface IFile
{
    const TYPE_IMAGE = 'image';
    const TYPE_TEXT = 'text';
    const TYPE_MEDIA = 'media';
    const TYPE_BINARY = 'binary';
    
    /**
     * @return string
     */
    public function setSum($sum);

    /**
     * @return int
     */
    public function setSize($size);

    /**
     * @return string
     */
    public function setExtension($extension);

    /**
     * @return string
     */
    public function setMimeType($mimeType);

    /**
     * @return string
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