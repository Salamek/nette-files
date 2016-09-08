<?php

namespace Salamek\Files;

use Nette;
use Nette\Utils\Image as NImage;

/**
 * Class ImagePipe
 * @package Salamek\Files
 */
class ImagePipe extends Nette\Object
{

    /** @var array */
    public $onBeforeSaveThumbnail = array();
    /** @var string */
    protected $assetsDir;
    /** @var string */
    protected $storageDir;
    /** @var string */
    protected $blankImage;
    /** @var string */
    private $wwwDir;
    /** @var string */
    private $path;
    /** @var string */
    private $baseUrl;

    /**
     * @param $assetsDir
     * @param $storageDir
     * @param $blankImage
     * @param $wwwDir
     * @param Nette\Http\Request $httpRequest
     */
    public function __construct($assetsDir, $storageDir, $blankImage, $wwwDir, Nette\Http\Request $httpRequest)
    {
        $this->wwwDir = $wwwDir;
        $this->assetsDir = $assetsDir;
        $this->storageDir = $storageDir;
        $this->blankImage = $blankImage;
        $this->baseUrl = rtrim($httpRequest->url->baseUrl, '/');
    }

    /**
     * @return string
     */
    public function getAssetsDir()
    {
        return $this->assetsDir;
    }

    /**
     * @param $dir
     */
    public function setAssetsDir($dir)
    {
        $this->assetsDir = $dir;
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
     * @param string $image
     * @param null $size
     * @param null $flags
     * @return string
     * @throws \Nette\Latte\CompileException
     * @throws FileNotFoundException;
     */
    public function request($image, $size = null, $flags = null)
    {
        $this->checkSettings();

        if ($image)
        {
            if ($image instanceof ImageProvider) {
                $image = $image->getFilename();
            }
            $originalFile = $this->assetsDir . "/" . $image;

            if (is_null($size))
            {
                return $this->getPath() . "/" . $image;
            }
        }
        elseif (empty($image) || is_null($image)) {
            if (!$this->blankImage || !file_exists($this->blankImage))
            {
                return "#";
            }

            $originalFile = $this->blankImage;
            $image = basename($this->blankImage);

            if (is_null($size))
            {
                return $this->blankImage;
            }
        }
        
        list($width, $height) = explode("x", $size);

        $thumbPath = "/" . $flags . "_" . $width . "x" . $height . "/" . $image;
        $thumbnailFile = $this->storageDir . $thumbPath;


        if ($flags == null) {
            $flags = NImage::FIT;
        } elseif (!is_int($flags)) {
            switch (strtolower($flags)):
                case "fit":
                    $flags = NImage::FIT;
                    break;
                case "fill":
                    $flags = NImage::FILL;
                    break;
                case "exact":
                    $flags = NImage::EXACT;
                    break;
                case "shrink_only":
                    $flags = NImage::SHRINK_ONLY;
                    break;
                case "stretch":
                    $flags = NImage::STRETCH;
                    break;
            endswitch;
            if (!isset($flags)) {
                throw new Nette\Latte\CompileException('Mode is not allowed');
            }
        }



        if (!file_exists($thumbnailFile)) {
            $this->mkdir(dirname($thumbnailFile));
            if (!file_exists($originalFile)) {
                throw new FileNotFoundException;
            }

            $img = NImage::fromFile($originalFile);
            if ($flags === "crop") {
                $img->crop('50%', '50%', $width, $height);
            } else {
                $img->resize($width, $height, $flags);
            }

            $this->onBeforeSaveThumbnail($img, $image, $width, $height, $flags);
            $img->save($thumbnailFile);
        }

        return $this->getPath() . $thumbPath;
    }

    /**
     * @throws \Nette\InvalidStateException
     */
    private function checkSettings()
    {
        if ($this->assetsDir == null) {
            throw new Nette\InvalidStateException("Assets directory is not setted");
        }
        if (!file_exists($this->assetsDir)) {
            throw new Nette\InvalidStateException("Assets directory '{$this->assetsDir}' does not exists");
        } elseif (!is_writeable($this->assetsDir)) {
            throw new Nette\InvalidStateException("Make assets directory '{$this->assetsDir}' writeable");
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
    private static function mkdir($dir)
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
