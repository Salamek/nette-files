<?php

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
namespace Salamek\Files;

use Nette;

/**
 * Class TImagePipe
 * @package Salamek\Files
 */
trait TImagePipe
{

    /** @var ImagePipe */
    public $imgPipe;


    /**
     * @param ImagePipe $imgPipe
     */
    public function injectImgPipe(ImagePipe $imgPipe)
    {
        $this->imgPipe = $imgPipe;
    }


    /**
     * @param string $class
     *
     * @return Nette\Templating\FileTemplate
     */
    protected function createTemplate($class = null)
    {
        $template = parent::createTemplate($class);
        /** @var \Nette\Templating\FileTemplate|\stdClass $template */
        $template->_imagePipe = $this->imgPipe;

        return $template;
    }
}