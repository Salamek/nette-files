<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files;

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
     * @return float
     */
    public static function parseSize(string $size): float
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }
}