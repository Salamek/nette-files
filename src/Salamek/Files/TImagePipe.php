<?php declare(strict_types = 1);

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
namespace Salamek\Files;

use Nette;

/**
 * Class TImagePipe
 * @package Salamek\Files
 * @deprecated
 */
trait TImagePipe
{

    /** @var ImagePipe */
    public $imgPipe;


    /**
     * @param ImagePipe $imgPipe
     * @deprecated
     */
    public function injectImgPipe(ImagePipe $imgPipe)
    {
        user_error('Dont use TImagePipe, it is deprecated, jus use latte filters/macros or autowire requested pipe service', E_USER_DEPRECATED);
        $this->imgPipe = $imgPipe;
    }


    /**
     * @param null $class
     * @return Nette\Templating\FileTemplate|\stdClass
     */
    protected function createTemplate($class = null)
    {
        $template = parent::createTemplate($class);
        /** @var \Nette\Templating\FileTemplate|\stdClass $template */
        $template->_imagePipe = $this->imgPipe;

        return $template;
    }
}