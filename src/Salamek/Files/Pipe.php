<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files;


use Nette\Http\Request;
use Nette\InvalidStateException;
use Nette\IOException;
use Nette\SmartObject;

/**
 * Class Pipe
 * @package Salamek\Files
 */
abstract class Pipe implements IPipe
{
    use SmartObject;

    /** @var string */
    protected $dataDir;

    /** @var string */
    protected $storageDir;

    /** @var string */
    private $path;

    /** @var string */
    private $baseUrl;

    /**
     * Pipe constructor.
     * @param $dataDir
     * @param $storageDir
     * @param Request $httpRequest
     */
    public function __construct(string $dataDir, string $storageDir, Request $httpRequest)
    {
        $this->dataDir = $dataDir;
        $this->storageDir = $storageDir;
        $this->baseUrl = rtrim($httpRequest->url->baseUrl, '/');

        $this->checkSettings();
    }

    /**
     * @return string
     */
    public function getDataDir(): string
    {
        return $this->dataDir;
    }

    /**
     * @param $dir
     */
    public function setDataDir(string $dir)
    {
        $this->dataDir = $dir;
    }

    /**
     * @return string
     */
    public function getStorageDir(): string
    {
        return $this->storageDir;
    }

    /**
     * @param string $storageDir
     */
    public function setStorageDir(string $storageDir): void
    {
        $this->storageDir = $storageDir;
    }

    /**
     * @throws InvalidStateException
     */
    private function checkSettings(): void
    {
        if ($this->dataDir == null) {
            throw new InvalidStateException("Assets directory is not setted");
        }
        if (!file_exists($this->dataDir)) {
            throw new InvalidStateException("Assets directory '{$this->dataDir}' does not exists");
        } elseif (!is_writeable($this->dataDir)) {
            throw new InvalidStateException("Make assets directory '{$this->dataDir}' writeable");
        }
        if ($this->getPath() == null) {
            throw new InvalidStateException("Path is not setted");
        }
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path !== null ? $this->path : $this->baseUrl . $this->storageDir;
    }

    /**
     * @param $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @param string $dir
     *
     * @throws \Nette\IOException
     * @return void
     */
    public static function mkdir(string $dir): void
    {
        $oldMask = umask(0);
        @mkdir($dir, 0777, true);
        @chmod($dir, 0777);
        umask($oldMask);

        if (!is_dir($dir) || !is_writable($dir)) {
            throw new IOException("Please create writable directory $dir.");
        }
    }
}