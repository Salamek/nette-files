<?php

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files;

use Latte\Engine;
use Latte\Runtime\FilterInfo;
use Nette;
use Salamek\Files\Models\IFile;
use Salamek\Files\Models\IStructureFile;


/**
 * Class TemplateHelpers
 * @package Salamek\Files
 */
class TemplateHelpers
{
    use Nette\SmartObject;

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
     * @param IFile|IStructureFile|null $file
     * @param null $size
     * @param null $flags
     * @return string
     * @throws Nette\Latte\CompileException
     */
    public function request($file = null, $size = null, $flags = null)
    {
        return $this->imagePipe->request($file, $size, $flags);
    }

    /**
     * @param FilterInfo $filterInfo
     * @param IFile|IStructureFile|null $file
     * @param null $size
     * @param null $flags
     * @return string
     * @throws Nette\Latte\CompileException
     */
    public function requestFilterAware(FilterInfo $filterInfo, $file = null, $size = null, $flags = null)
    {
        return $this->imagePipe->request($file, $size, $flags);
    }
}
