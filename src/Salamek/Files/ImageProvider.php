<?php

namespace Salamek\Files;

interface ImageProvider
{

    /**
     * @return string
     */
    public static function getNamespace();


    /**
     * @return string
     */
    public function getFilename();

}