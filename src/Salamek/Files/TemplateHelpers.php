<?php

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files;

use Latte\Engine;
use Latte\Runtime\FilterInfo;
use Nette;
use Salamek\Files\Models\IFile;


/**
 * Class TemplateHelpers
 * @package Salamek\Files
 */
class TemplateHelpers extends Nette\Object
{

    /**
     * @var ImagePipe
     */
    private $imagePipe;

    /**
     * TemplateHelpers constructor.
     * @param ImagePipe $imagePipe
     */
    public function __construct(ImagePipe $imagePipe)
    {
        $this->imagePipe = $imagePipe;
    }

    /**
     * @param Engine $engine
     */
    public function register(Engine $engine)
    {
        if (class_exists('Latte\Runtime\FilterInfo')) {
            $engine->addFilter('request', [$this, 'requestFilterAware']);
        } else {
            $engine->addFilter('request', [$this, 'request']);
        }
        $engine->addFilter('getImagePipe', [$this, 'getImagePipe']);
    }


    /**
     * @return ImagePipe
     */
    public function getImagePipe()
    {
        return $this->imagePipe;
    }

    /**
     * @param IFile|null $file
     * @param null $size
     * @param null $flags
     * @return string
     * @throws Nette\Latte\CompileException
     */
    public function request(IFile $file = null, $size = null, $flags = null)
    {
        return $this->imagePipe->request($file, $size, $flags);
    }

    /**
     * @param FilterInfo $filterInfo
     * @param IFile|null $file
     * @param null $size
     * @param null $flags
     * @return string
     * @throws Nette\Latte\CompileException
     */
    public function requestFilterAware(FilterInfo $filterInfo, IFile $file = null, $size = null, $flags = null)
    {
        return $this->imagePipe->request($file, $size, $flags);
    }
}
