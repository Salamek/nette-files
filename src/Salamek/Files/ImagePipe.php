<?php

namespace Salamek\Files;

use Nette;
use Nette\Utils\Image as NImage;
use Salamek\Files\Models\IFile;
use Salamek\Files\Models\IStructureFile;

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
     * @param IFile|IStructureFile|null $file
     * @param null $size
     * @param null $flags
     * @return string
     * @throws \Nette\Latte\CompileException
     * @throws FileNotFoundException;
     */
    public function request($file = null, $size = null, $flags = null)
    {
        if ($file instanceof IStructureFile) {
            $file = $file->getFile();
        }

        if ($file) {
            if ($file->getType() != IFile::TYPE_IMAGE) {
                throw new \InvalidArgumentException('$file is not an image');
            }

            $originalFile = $this->assetsDir . "/" . $file->getBasename();
            $image = $file->getBasename();
            if (is_null($size)) {
                return $this->getPath() . "/" . $file->getBasename();
            }
        } elseif (is_null($file)) {
            if (!$this->blankImage || !file_exists($this->blankImage)) {
                return "#";
            }

            return str_replace($this->wwwDir, '', $this->blankImage);
        }

        list($width, $height) = explode("x", $size);

        $thumbPath = "/" . $flags . "_" . $width . "x" . $height . "/" . $image;
        $thumbnailFile = $this->storageDir . $thumbPath;


        if ($flags == null) {
            $imageFlags = NImage::FIT;
        } elseif (!is_int($flags)) {
            switch (strtolower($flags)) {
                case "fit":
                    $imageFlags = NImage::FIT;
                    break;
                case "fill":
                    $imageFlags = NImage::FILL;
                    break;
                case "exact":
                    $imageFlags = NImage::EXACT;
                    break;
                case "shrink_only":
                    $imageFlags = NImage::SHRINK_ONLY;
                    break;
                case "stretch":
                    $imageFlags = NImage::STRETCH;
                    break;
                case 'fit_exact':
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
            } elseif ($flags === "fit_exact") {
                $blank = NImage::fromBlank($width, $height, NImage::rgb(255,255,255,127));
                $img->resize($width, $height, NImage::FIT);

                $blank->place($img, '50%', '50%');

                $img = $blank;
            } else {
                $img->resize($width, $height, $imageFlags);
                $img->sharpen();
            }

            $this->onBeforeSaveThumbnail($img, $file, $width, $height, $flags);

            $img->save($thumbnailFile);
        }

        return $this->getPath() . $thumbPath;
    }
}
