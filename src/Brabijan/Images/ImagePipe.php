<?php

namespace Brabijan\Images;

use Nette;
use Nette\Utils\Image as NImage;

/**
 * @author Jan Brabec <brabijan@gmail.com>
 */
class ImagePipe extends Nette\Object
{

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

    /** @var string|null */
    protected $namespace = null;

    /** @var array */
    public $onBeforeSaveThumbnail = array();


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
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
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
    public function getAssetsDir()
    {
        return $this->assetsDir;
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
    public function getStorageDir()
    {
        return $this->storageDir;
    }

    /**
     * @param string $blankImage
     */
    public function setBlankImage($blankImage)
    {
        $this->blankImage = $blankImage;
    }

    /**
     * @return string
     */
    public function getBlankImage()
    {
        return $this->blankImage;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path !== null ? $this->path : $this->baseUrl . str_replace($this->wwwDir, '', $this->storageDir);
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
     * @param $namespace
     * @return $this
     */
    public function setNamespace($namespace)
    {
        if (empty($namespace)) {
            $this->namespace = null;
        } else {
            $this->namespace = $namespace . "/";
        }

        return $this;
    }


    /**
     * @param string $image
     * @param null $size
     * @param null $flags
     * @param bool $strictMode
     * @return string
     * @throws \Nette\Latte\CompileException
     * @throws FileNotFoundException;
     */
    public function request($image, $size = null, $flags = null, $strictMode = false)
    {
        $this->checkSettings();
        if ($image instanceof ImageProvider) {
            $this->setNamespace($image->getNamespace());
            $image = $image->getFilename();
        } elseif (empty($image)) {
            return "#";
        }
        if ($size === null) {
            return $this->getPath() . "/" . $this->namespace . "/" . $image;
        }

        list($width, $height) = explode("x", $size);
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

        $thumbPath = "/" . $this->namespace . $flags . "_" . $width . "x" . $height . "/" . $image;

        $thumbnailFile = $this->storageDir . $thumbPath;
        $originalFile = $this->assetsDir . "/" . $this->namespace . "/" . $image;

        if (!file_exists($thumbnailFile)) {
            $this->mkdir(dirname($thumbnailFile));
            if (file_exists($originalFile)) {
                $img = NImage::fromFile($originalFile);
                if ($flags === "crop") {
                    $img->crop('50%', '50%', $width, $height);
                } else {
                    $img->resize($width, $height, $flags);
                }

                $this->onBeforeSaveThumbnail($img, $this->namespace, $image, $width, $height, $flags);
                $img->save($thumbnailFile);
            } elseif ($strictMode) {
                throw new FileNotFoundException;
            }
        }
        $this->namespace = null;

        return $this->getPath() . $thumbPath;
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
