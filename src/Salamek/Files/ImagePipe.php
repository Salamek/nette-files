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
     * @param IFile|null $file
     * @param string|null $size
     * @param string|null $flags
     * @return string
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

            $originalFile = $this->fileStorage->getFileSystemPath($file);
            if (strpos($file->getMimeType(), 'svg') !== false) {
                // Image is SVG, use special resize for SVG
                $generator = function ($thumbnailFile) use ($originalFile, $width, $height): void {
                    Tools::resizeSvgImage($originalFile, $width, $height)->save($thumbnailFile);
                };
            } else if (strpos($file->getMimeType(), 'icon') !== false) {
                // Image is ico, we do not support those, but they are image, copy them
                $generator = function ($thumbnailFile) use ($originalFile, $width, $height, $flags): void {
                    copy($originalFile, $thumbnailFile);
                };
            } else {
                $generator = function ($thumbnailFile) use ($originalFile, $width, $height, $flags): void {
                    Tools::resizeImage($originalFile, $width, $height, $flags)->save($thumbnailFile);
                };
            }

            $image = $file->getBasename();
        } else {
            if (!$width) {
                $width = 100;
            }
            $generator = function ($thumbnailFile) use ($width, $height): void {
                Tools::generateImagePlaceholder($width, ($height ? $height : null))->save($thumbnailFile);
            };

            $image = 'placeholder.jpg';
        }

        $thumbPath = '/' . $flags . '_' . $width . 'x' . $height . '/' . $image;
        $thumbnailFile = $this->fileStorage->getWebTempDir() . $thumbPath;

        if (!file_exists($thumbnailFile)) {

            Tools::mkdir(dirname($thumbnailFile));
            $generator($thumbnailFile);
        }

        return $this->getRelativeWebTempPath() . $thumbPath;
    }
}
