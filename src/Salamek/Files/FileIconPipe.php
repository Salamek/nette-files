<?php declare(strict_types = 1);

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
namespace Salamek\Files;


use Salamek\Files\Models\IFile;

/**
 * Class ImagePipe
 * @package Salamek\Files
 */
class FileIconPipe extends Pipe
{
    /**
     * @param IFile|null $file
     * @param string|null $size
     * @return string
     */
    public function request(IFile $file = null, string $size = null): string
    {
        if (is_null($size)){
            [$width, $height,] = [null, null];
        } else {
            $parts = explode('x', $size);
            $width = ($parts[0] ? intval($parts[0]) : null);
            $height = ($parts[1] ? intval($parts[1]) : null);
        }

        if ($file) {
            $originalFile = $this->fileStorage->getIconFileSystemPath($file);
            if (strpos($file->getMimeType(), 'svg') !== false) {
                $generator = function ($thumbnailFile) use ($originalFile, $width, $height): void {
                    Tools::resizeSvgImage($originalFile, $width, $height)->save($thumbnailFile);
                };
            } else {
                $generator = function ($thumbnailFile) use ($originalFile, $width, $height): void {
                    Tools::resizeImage($originalFile, $width, $height)->save($thumbnailFile);
                };
            }

            $image = $this->fileStorage->getIconBaseName($file);
        } else {
            if (!$width) {
                $width = 64;
            }
            $generator = function ($thumbnailFile) use ($width, $height): void {
                Tools::generateImagePlaceholder($width, ($height ? $height : null))->save($thumbnailFile);
            };

            $image = 'placeholder.jpg';
        }

        $iconTmpPath = '/icon_' . $width . 'x' . $height . '/' . $image;
        $iconTmpFile = $this->fileStorage->getWebTempDir() . $iconTmpPath;

        if (!file_exists($iconTmpFile)) {

            $this->fileStorage->mkdir(dirname($iconTmpFile));
            $generator($iconTmpFile);
        }

        return $this->getRelativeWebTempPath() . $iconTmpPath;
    }
}
