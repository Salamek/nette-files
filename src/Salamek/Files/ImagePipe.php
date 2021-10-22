<?php declare(strict_types = 1);

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
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
     * @param IStructureFile|null $structureFile
     * @param int|null $size
     * @param null $flags
     * @return string
     * @throws Nette\Utils\ImageException
     * @throws Nette\Utils\UnknownImageFileException
     */
    public function request(IStructureFile $structureFile = null, int $size = null, $flags = null): string
    {
        if ($structureFile) {
            $file = $structureFile->getFile();
            if ($file->getType() != IFile::TYPE_IMAGE) {
                throw new \InvalidArgumentException('$file is not an image');
            }

            $originalFile = $this->dataDir . "/" . $file->getBasename();
            $image = $file->getBasename();
            if (is_null($size)) {
                return str_replace($this->wwwDir, '', $this->getDataDir()). "/" . $file->getBasename();
            }
        } else {
            throw new Nette\NotImplementedException('Empty image generator');
        }

        list($width, $height) = explode("x", $size);

        $thumbPath = "/" . $flags . "_" . $width . "x" . $height . "/" . $image;
        $thumbnailFile = $this->storageDir . $thumbPath;


        if (is_null($flags)) {
            $imageFlags = NImage::FIT;
        } else {
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
                    throw new \InvalidArgumentException('Mode is not allowed');
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

            $this->onBeforeSaveThumbnail($img, $structureFile, $width, $height, $flags);

            $img->save($thumbnailFile);
        }

        return $this->getPath() . $thumbPath;
    }
}
