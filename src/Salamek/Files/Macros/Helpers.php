<?php

namespace Salamek\Files\Macros;

use Nette;

/**
 * Class Helpers
 * @package Salamek\Files\Macros
 */
class Helpers extends Nette\Object
{

    public static function prepareMacroArguments($macro)
    {
        $arguments = array_map(function ($value) {
            return trim($value);
        }, explode(",", $macro));

        $namespace = null;
        $name = $arguments[0];
        $size = (isset($arguments[1]) AND !empty($arguments[1])) ? $arguments[1] : null;
        $flags = (isset($arguments[2]) AND !empty($arguments[2])) ? $arguments[2] : null;
        
        return array(
            "name" => $name,
            "size" => $size,
            "flags" => $flags
        );
    }

}