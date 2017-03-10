<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files;

use Nette;

/**
 * Class Pipe
 * @package Salamek\Files
 */
abstract class Pipe extends Nette\Object implements IPipe
{
    /** @var string */
    protected $dataDir;
    /** @var string */
    protected $storageDir;
    /** @var string */
    protected $blankImage;
    /** @var string */
    protected $wwwDir;
    /** @var string */
    private $path;
    /** @var string */
    private $baseUrl;

    /**
     * @param $dataDir
     * @param $storageDir
     * @param $blankImage
     * @param $wwwDir
     * @param Nette\Http\Request $httpRequest
     */
    public function __construct($dataDir, $storageDir, $blankImage, $wwwDir, Nette\Http\Request $httpRequest)
    {
        $this->wwwDir = $wwwDir;
        $this->dataDir = $dataDir;
        $this->storageDir = $storageDir;
        $this->blankImage = $blankImage;
        $this->baseUrl = rtrim($httpRequest->url->baseUrl, '/');

        $this->checkSettings();
    }

    /**
     * @return string
     */
    public function getDataDir()
    {
        return $this->dataDir;
    }

    /**
     * @param $dir
     */
    public function setDataDir($dir)
    {
        $this->dataDir = $dir;
    }

    /**
     * @return string
     */
    public function getStorageDir()
    {
        return $this->storageDir;
    }

    /**
     * @param string $storageDir
     */
    public function setStorageDir($storageDir)
    {
        $this->storageDir = $storageDir;
    }

    /**
     * @return string
     */
    public function getBlankImage()
    {
        return $this->blankImage;
    }

    /**
     * @param string $blankImage
     */
    public function setBlankImage($blankImage)
    {
        $this->blankImage = $blankImage;
    }

    /**
     * @throws \Nette\InvalidStateException
     */
    private function checkSettings()
    {
        if ($this->dataDir == null) {
            throw new Nette\InvalidStateException("Assets directory is not setted");
        }
        if (!file_exists($this->dataDir)) {
            throw new Nette\InvalidStateException("Assets directory '{$this->dataDir}' does not exists");
        } elseif (!is_writeable($this->dataDir)) {
            throw new Nette\InvalidStateException("Make assets directory '{$this->dataDir}' writeable");
        }
        if ($this->getPath() == null) {
            throw new Nette\InvalidStateException("Path is not setted");
        }
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path !== null ? $this->path : $this->baseUrl . str_replace($this->wwwDir, '', $this->storageDir);
    }

    /**
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @param string $dir
     *
     * @throws \Nette\IOException
     * @return void
     */
    public static function mkdir($dir)
    {
        $oldMask = umask(0);
        @mkdir($dir, 0777, true);
        @chmod($dir, 0777);
        umask($oldMask);

        if (!is_dir($dir) || !is_writable($dir)) {
            throw new Nette\IOException("Please create writable directory $dir.");
        }
    }
}