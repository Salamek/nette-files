<?php

namespace Salamek\Files;

use Nette;

/**
 * Class Image
 * @package Salamek\Files
 */
class Image extends Nette\Object
{

    /** @var string */
    private $file;

    /** @var Size */
    private $size;


    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = $file;
        $this->size = Size::fromFile($file);
    }


    /**
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->file);
    }

    /**
     * @return Size
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getBasename();
    }

    /**
     * @return string
     */
    public function getBasename()
    {
        return basename($this->getFile());
    }

    /**
     * @return float|int
     */
    public function getFile()
    {
        return $this->file;
    }

}