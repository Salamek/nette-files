<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Salamek\Files;

use Latte\Engine;
use Latte\Runtime\FilterInfo;
use Nette;
use Salamek\Files\Models\IFile;


/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class TemplateHelpers extends Nette\Object
{

	/**
	 * @var ImagePipe
	 */
	private $imagePipe;



	public function __construct(ImagePipe $imagePipe)
	{
		$this->imagePipe = $imagePipe;
	}



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



	public function request(IFile $file = null, $size = null, $flags = null)
	{
		return $this->imagePipe->request($file, $size, $flags);
	}



	public function requestFilterAware(FilterInfo $filterInfo, IFile $file = null, $size = null, $flags = null)
	{
		return $this->imagePipe->request($file, $size, $flags);
	}



	/**
	 * @deprecated
	 */
	public function loader($method)
	{
		if (method_exists($this, $method) && strtolower($method) !== 'register') {
			return [$this, $method];
		}
	}

}
