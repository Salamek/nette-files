<?php

namespace Salamek\Files;

use Nette;
use Nette\Http\FileUpload;
use Nette\Utils\Finder;
use Nette\Utils\Random;


/**
 * Class ImageStorage
 * @package Salamek\Files
 */
class ImageStorage extends Nette\Object
{

    /** @var string */
    private $imagesDir;

    /** @var string */
    private $namespace = null;

    private $originalPrefix = "original";

    /** @var array */
    public $onUploadImage = array();


    /**
     * @param string $dir
     */
    public function __construct($dir)
    {
        if (!is_dir($dir)) {
            umask(0);
            mkdir($dir, 0777);
        }
        $this->imagesDir = $dir;
    }


    /**
     * @param $originalPrefix
     */
    public function setOriginalPrefix($originalPrefix)
    {
        $this->originalPrefix = $originalPrefix;
    }


    /**
     * @param $namespace
     * @return $this
     */
    public function setNamespace($namespace)
    {
        if ($namespace === null) {
            $this->namespace = null;
        } else {
            $this->namespace = $namespace . "/";
        }

        return $this;
    }


    /**
     * @param string $namespace
     * @return bool
     */
    public function isNamespaceExists($namespace)
    {
        return file_exists($this->imagesDir . "/" . $namespace);
    }


    /**
     * @param $dir
     */
    public function setImagesDir($dir)
    {
        if (!is_dir($dir)) {
            umask(0);
            mkdir($dir, 0777);
        }
        $this->imagesDir = $dir;
    }


    /**
     * @param FileUpload $file
     * @return Image
     * @throws \Nette\InvalidArgumentException
     */
    public function upload(FileUpload $file)
    {
        if (!$file->isOk() || !$file->isImage()) {
            throw new Nette\InvalidArgumentException;
        }

        do {
            $name = Random::generate() . '.' . $file->getSanitizedName();
        } while (file_exists($path = $this->imagesDir . "/" . $this->namespace . $this->originalPrefix . "/" . $name));

        $file->move($path);
        $this->onUploadImage($path, $this->namespace);
        $this->namespace = null;

        return new Image($path);
    }


    /**
     * @param string $content
     * @param string $filename
     * @return Image
     */
    public function save($content, $filename)
    {
        do {
            $name = Random::generate() . '.' . $filename;
        } while (file_exists($path = $this->imagesDir . "/" . $this->namespace . $this->originalPrefix . "/" . $name));

        @mkdir(dirname($path), 0777, true); // @ - dir may already exist
        file_put_contents($path, $content);

        return new Image($path);
    }


    /**
     * @param $filename
     * @throws \Nette\InvalidStateException
     */
    public function deleteFile($filename)
    {
        if (empty($filename)) {
            throw new Nette\InvalidStateException('Filename was not provided');
        }
        /** @var $file \SplFileInfo */
        foreach (Finder::findFiles($filename)->from($this->imagesDir . ($this->namespace ? "/" . $this->namespace : "")) as $file) {
            @unlink($file->getPathname());
        }
        $this->namespace = null;
    }


    /**
     * @return string
     */
    public function getImagesDir()
    {
        return $this->imagesDir;
    }

}


/**
 * Class FileNotFoundException
 * @package Salamek\Files
 */
class FileNotFoundException extends \RuntimeException
{

}