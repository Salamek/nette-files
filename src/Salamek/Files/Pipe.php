<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files;


use Nette\Http\Request;
use Nette\SmartObject;

/**
 * Class Pipe
 * @package Salamek\Files
 */
abstract class Pipe implements IPipe
{
    use SmartObject;

    /** @var FileStorage */
    protected $fileStorage;

    /** @var string */
    protected $webTempPath;

    /** @var string */
    private $baseUrl;

    /**
     * Pipe constructor.
     * @param FileStorage $fileStorage
     * @param string $webTempPath
     * @param Request $httpRequest
     */
    public function __construct(FileStorage $fileStorage, string $webTempPath, Request $httpRequest)
    {
        $this->fileStorage = $fileStorage;
        $this->webTempPath = $webTempPath;
        $this->baseUrl = rtrim($httpRequest->url->baseUrl, '/');
    }

    /**
     * @return FileStorage
     */
    public function getFileStorage(): FileStorage
    {
        return $this->fileStorage;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getDataDir(): string
    {
        user_error('Pipe::getDataDir is deprecated, use Pipe->getFileStorage()->getDataDir() instead', E_USER_DEPRECATED);
        return $this->fileStorage->getDataDir();
    }

    /**
     * @return string
     * @deprecated
     */
    public function getWebTempDir(): string
    {
        user_error('Pipe::getWebTempDir is deprecated, use Pipe->getFileStorage()->getWebTempDir() instead', E_USER_DEPRECATED);
        return $this->fileStorage->getWebTempDir();
    }

    /**
     * @return string
     */
    public function getWebTempPath(): string
    {
        return $this->webTempPath;
    }

    /**
     * @return string
     */
    public function getRelativeWebTempPath(): string
    {
        return $this->baseUrl .'/'. $this->webTempPath;
    }
}