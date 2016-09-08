<?php

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


    protected function registerTexyMacros(\Texy $texy)
    {
        Macros\Texy::register($texy, $this->imgPipe);
    }

}