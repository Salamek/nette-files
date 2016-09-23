<?php

namespace Salamek\Files\DI;

use Latte;
use Nette;
use Nette\DI\Compiler;
use Nette\DI\Configurator;


if (!class_exists('Nette\DI\CompilerExtension')) {
    class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
    class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
    class_alias('Nette\Config\Configurator', 'Nette\DI\Configurator');
}

if (!class_exists('Latte\Engine')) {
    class_alias('Nette\Latte\Engine', 'Latte\Engine');
}

/**
 * Class FilesExtension
 * @package Salamek\Files\DI
 */
class FilesExtension extends Nette\DI\CompilerExtension
{

    public function loadConfiguration()
    {
        $config = $this->getConfig();
        $builder = $this->getContainerBuilder();
        $engine = $builder->getDefinition('nette.latteFactory');

        $install = 'Salamek\Files\Macros\Latte::install';

        if (method_exists('Latte\Engine', 'getCompiler')) {
            $engine->addSetup('Salamek\Files\Macros\Latte::install(?->getCompiler())', array('@self'));
        } else {
            $engine->addSetup($install . '(?->compiler)', array('@self'));
        }

        $builder->addDefinition($this->prefix('imagePipe'))
            ->setClass('Salamek\Files\ImagePipe', array($config['assetsDir'], $config['storageDir'], $config['blankImage'], $this->getContainerBuilder()->parameters['wwwDir']))
            ->addSetup('setAssetsDir', array($config['assetsDir']))
            ->addSetup('getBlankImage', array($config['blankImage']))
            ->addSetup('setStorageDir', array($config['storageDir']));
        
        $builder->addDefinition($this->prefix('fileStorage'))->setClass('Salamek\Files\FileStorage', array($config['assetsDir']));
    }


    /**
     * @param \Nette\Config\Configurator $config
     * @param string $extensionName
     */
    public static function register(Configurator $config, $extensionName = 'filesExtension')
    {
        $config->onCompile[] = function (Configurator $config, Compiler $compiler) use ($extensionName) {
            $compiler->addExtension($extensionName, new FilesExtension());
        };
    }


    /**
     * {@inheritdoc}
     */
    public function getConfig(array $defaults = null, $expand = true)
    {
        $defaults = array(
            'storageDir' => $this->getContainerBuilder()->parameters['wwwDir'] . '/assets',
            'assetsDir' => $this->getContainerBuilder()->parameters['wwwDir'] . '/assets',
            'blankImage' => $this->getContainerBuilder()->parameters['wwwDir'] . '/assets/blank.png'
        );

        return parent::getConfig($defaults, $expand);
    }

}
