<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files;

use Nette\Utils\Image;

/**
 * Class Tools
 * @package Salamek\Files
 */
class Tools
{
    /**
     * @return int
     */
    public static function getMaxUploadSize(): int
    {
        $max_size = -1;

        if ($max_size < 0) {
            $max_size = self::parseSize(ini_get('post_max_size'));

            $upload_max = self::parseSize(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        return $max_size;
    }

    /**
     * @param string $size
     * @return int
     */
    public static function parseSize(string $size): int
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        if ($unit) {
            $calculatedSize = round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            $calculatedSize = round($size);
        }

        return intval($calculatedSize);
    }

    public static function generateImagePlaceholder(int $width, int $height = null): Image {
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
     * @param string $sourcePath
     * @param int|null $width
     * @param int|null $height
     * @param string|null $flags
     * @return Image
     * @throws \Nette\Utils\UnknownImageFileException
     */
    public static function resizeImage(string $sourcePath, int $width = null, int $height = null, string $flags = null): Image {
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

    /**
     * @param string $sourcePath
     * @param int|null $width
     * @param int|null $height
     * @return \DOMDocument
     */
    public static function resizeSvgImage(string $sourcePath, int $width = null, int $height = null): \DOMDocument {

        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->load($sourcePath);
        $svg = $dom->documentElement;

        $pattern = '/^(\d*\.\d+|\d+)(px)?$/'; // positive number, px unit optional
        $matchWidth = preg_match( $pattern, $svg->getAttribute('width'), $detectedWidthInfo);
        $matchHeight = preg_match( $pattern, $svg->getAttribute('height'), $detectedHeightInfo);
        $interpretable = $matchWidth && $matchHeight;

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

        return $dom;
    }

}
