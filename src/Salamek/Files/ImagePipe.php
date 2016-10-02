<?php

namespace Salamek\Files;

use Nette;
use Nette\Utils\Image as NImage;
use Salamek\Files\Models\IFile;

/**
 * Class ImagePipe
 * @package Salamek\Files
 */
class ImagePipe extends Pipe
{

    /** @var array */
    public $onBeforeSaveThumbnail = [];


    /**
     * @return TemplateHelpers
     */
    public function createTemplateHelpers()
    {
        return new TemplateHelpers($this);
    }

    /**
     * @param IFile|null $file
     * @param null $size
     * @param null $flags
     * @return string
     * @throws \Nette\Latte\CompileException
     * @throws FileNotFoundException;
     */
    public function request(IFile $file = null, $size = null, $flags = null)
    {
        if ($file->getType() != IFile::TYPE_IMAGE)
        {
            throw new \InvalidArgumentException('$file is not an image');
        }
        
        if ($file)
        {
            $originalFile = $this->assetsDir . "/" . $file->getBasename();
            $image = $file->getBasename();
            if (is_null($size))
            {
                return $this->getPath() . "/" . $file->getBasename();
            }
        }
        elseif (is_null($file)) {
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
            switch (strtolower($flags)) {
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
                case 'crop':
                    break;
                default:
                    throw new Nette\Latte\CompileException('Mode is not allowed');
                    break;
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

            $this->onBeforeSaveThumbnail($img, $file, $width, $height, $flags);
            $img->save($thumbnailFile);
        }

        return $this->getPath() . $thumbPath;
    }
}
