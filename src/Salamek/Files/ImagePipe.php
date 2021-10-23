<?php declare(strict_types = 1);

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
namespace Salamek\Files;

use Nette;
use Nette\Utils\Image;
use Salamek\Files\Models\IFile;

/**
 * Class ImagePipe
 * @package Salamek\Files
 */
class ImagePipe extends Pipe
{
    /**
     * @param int $width
     * @param int|null $height
     * @return Image
     */
    private function generateImagePlaceholder(int $width, int $height = null): Image {
        $usedHeight = ($height ? $height : $width);
        $image = Image::fromBlank($width, $usedHeight, Image::rgb(204, 204, 204));
        $fontFile = __DIR__.'/RobotoMono-Regular.ttf';
        $text = sprintf('%sx%s', $width, $usedHeight);
        $fontSize = intval($usedHeight / 4);

        $textBox = imagettfbbox($fontSize, 0, $fontFile, $text);

        while ($textBox[4] >= $width) {
            $fontSize -= round($fontSize / 2);
            $textBox  = imagettfbbox($fontSize, 0, $fontFile, $text);
            if ($fontSize <= 9) {
                $fontSize = 9;
                break;
            }
        }
        $textWidth  = abs($textBox[4] - $textBox[0]);
        $textHeight = abs($textBox[5] - $textBox[1]);
        $textX      = intval(($width - $textWidth) / 2);
        $textY      = intval(($usedHeight + $textHeight) / 2);

        $image->ftText($fontSize, 0, $textX, $textY, Image::rgb(150, 150, 150), $fontFile, $text);

        return $image;
    }

    /**
     * @param $sourcePath
     * @param int|null $width
     * @param int|null $height
     * @param string|null $flags
     * @return Image
     * @throws Nette\Utils\UnknownImageFileException
     */
    private function resizeImage(string $sourcePath, int $width = null, int $height = null, string $flags = null): Image {
        if (is_null($flags)) {
            $imageFlags = Image::FIT;
        } else {
            switch (strtolower($flags)) {
                case "fit":
                    $imageFlags = Image::FIT;
                    break;
                case "fill":
                    $imageFlags = Image::FILL;
                    break;
                case "exact":
                    $imageFlags = Image::EXACT;
                    break;
                case "shrink_only":
                    $imageFlags = Image::SHRINK_ONLY;
                    break;
                case "stretch":
                    $imageFlags = Image::STRETCH;
                    break;
                case 'fit_exact':
                case 'crop':
                    $imageFlags = null;
                    break;
                default:
                    throw new \InvalidArgumentException('Mode is not allowed');
                    break;
            }
        }

        if (!file_exists($sourcePath)) {
            throw new FileNotFoundException;
        }

        if (is_null($width) && is_null($height)) {
            [$width, $height] = getimagesize($sourcePath);
        }

        $img = Image::fromFile($sourcePath);

        if ($flags === "crop") {
            $img->crop('50%', '50%', $width, $height);
        } elseif ($flags === "fit_exact") {
            $blank = Image::fromBlank($width, $height, Image::rgb(255,255,255,127));
            $img->resize($width, $height, Image::FIT);

            $blank->place($img, '50%', '50%');

            $img = $blank;
        } else {
            $img->resize($width, $height, $imageFlags);
            $img->sharpen();
        }

        return $img;
    }

    private function resizeSvgImage(string $sourcePath, string $thumbnailFile, int $width = null, int $height = null): void {

        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->load($sourcePath);
        $svg = $dom->documentElement;

        $pattern = '/^(\d*\.\d+|\d+)(px)?$/'; // positive number, px unit optional
        $interpretable =  preg_match( $pattern, $svg->getAttribute('width'), $detectedWidthInfo) &&
            preg_match( $pattern, $svg->getAttribute('height'), $detectedHeightInfo);

        if ($interpretable) {
            $detectedWidth = $detectedWidthInfo[0];
            $detectedHeight = $detectedHeightInfo[0];

            if (!$svg->hasAttribute('viewBox') ) {
                // userspace coordinates
                $view_box = implode(' ', [0, 0, $detectedWidth, $detectedHeight]);
                $svg->setAttribute('viewBox', $view_box);
            }

            if (is_null($width) && $height) {
                $ratio = $detectedHeight / $height;
                $width = intval(round($detectedWidth / $ratio));
            } elseif (is_null($height) && $width) {
                $ratio = $detectedWidth / $width;
                $height = intval(round($detectedHeight / $ratio));
            }

            $svg->setAttribute('width', strval($width));
            $svg->setAttribute('height', strval($height));
        }


        $dom->save($thumbnailFile);
    }

    /**
     * @param IFile|null $file
     * @param string|null $size
     * @param string|null $flags
     * @return string
     * @throws Nette\Utils\ImageException
     */
    public function request(IFile $file = null, string $size = null, string $flags = null): string
    {
        if (is_null($size)){
            [$width, $height,] = [null, null];
        } else {
            $parts = explode('x', $size);
            $width = ($parts[0] ? intval($parts[0]) : null);
            $height = ($parts[1] ? intval($parts[1]) : null);
        }

        if ($file) {
            if ($file->getType() != IFile::TYPE_IMAGE) {
                throw new \InvalidArgumentException('$file is not an image');
            }

            $originalFile = $this->dataDir . '/' . $file->getBasename();
            if (strpos($file->getMimeType(), 'svg') !== false) {
                $generator = function ($thumbnailFile) use ($originalFile, $width, $height): void {
                    $this->resizeSvgImage($originalFile, $thumbnailFile, $width, $height);
                };
            } else {
                $generator = function ($thumbnailFile) use ($originalFile, $width, $height, $flags): void {
                    $this->resizeImage($originalFile, $width, $height, $flags)->save($thumbnailFile);
                };
            }

            $image = $file->getBasename();
        } else {
            if (!$width) {
                $width = 100;
            }
            $generator = function ($thumbnailFile) use ($width, $height): void {
                $this->generateImagePlaceholder($width, ($height ? $height : null))->save($thumbnailFile);
            };

            $image = 'placeholder';
        }

        $thumbPath = '/' . $flags . '_' . $width . 'x' . $height . '/' . $image;
        $thumbnailFile = $this->webTempDir . $thumbPath;

        if (!file_exists($thumbnailFile)) {

            $this->mkdir(dirname($thumbnailFile));
            $generator($thumbnailFile);
        }

        return $this->getPath() . $thumbPath;
    }
}
