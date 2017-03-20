<?php

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
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

        $builder->addDefinition($this->prefix('imagePipe'))
            ->setClass('Salamek\Files\ImagePipe', array($config['dataDir'], $config['storageDir'], $config['blankImage'], $this->getContainerBuilder()->parameters['wwwDir']))
            ->addSetup('setDataDir', array($config['dataDir']))
            ->addSetup('setBlankImage', array($config['blankImage']))
            ->addSetup('setStorageDir', array($config['storageDir']));

        $builder->addDefinition($this->prefix('helpers'))
            ->setClass('Salamek\Files\TemplateHelpers')
            ->setFactory($this->prefix('@imagePipe') . '::createTemplateHelpers')
            ->setInject(FALSE);
        
        $builder->addDefinition($this->prefix('fileStorage'))->setClass('Salamek\Files\FileStorage', array($config['dataDir'], $config['iconDir'], $this->getContainerBuilder()->parameters['wwwDir']));
        
        $builder->addDefinition($this->prefix('fileFiltersLatte'))->setClass('Salamek\Files\Filters\Latte', []);
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
            'dataDir' => $this->getContainerBuilder()->parameters['wwwDir'] . '/assets',
            'blankImage' => $this->getContainerBuilder()->parameters['wwwDir'] . '/assets/blank.png'
        );

        return parent::getConfig($defaults, $expand);
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();
        $registerToLatte = function (Nette\DI\ServiceDefinition $def) {
            $def->addSetup('?->onCompile[] = function($engine) { Salamek\Files\Macros\Latte::install($engine->getCompiler()); }', ['@self']);

            if (method_exists('Latte\Engine', 'addProvider')) { // Nette 2.4
                $def->addSetup('addProvider', ['imagePipe', $this->prefix('@imagePipe')])
                    ->addSetup('addFilter', ['request', [$this->prefix('@helpers'), 'requestFilterAware']])
                    ->addSetup('addFilter', ['fileIcon', [$this->prefix('@fileFiltersLatte'), 'fileIcon']]);
            } else {
                $def->addSetup('addFilter', ['getImagePipe', [$this->prefix('@helpers'), 'getImagePipe']])
                    ->addSetup('addFilter', ['request', [$this->prefix('@helpers'), 'request']])
                    ->addSetup('addFilter', ['fileIcon', [$this->prefix('@fileFiltersLatte'), 'fileIcon']]);
            }
        };

        $latteFactoryService = $builder->getByType('Nette\Bridges\ApplicationLatte\ILatteFactory');
        if (!$latteFactoryService || !self::isOfType($builder->getDefinition($latteFactoryService)->getClass(), 'Latte\engine')) {
            $latteFactoryService = 'nette.latteFactory';
        }

        if ($builder->hasDefinition($latteFactoryService) && self::isOfType($builder->getDefinition($latteFactoryService)->getClass(), 'Latte\Engine')) {
            $registerToLatte($builder->getDefinition($latteFactoryService));
        }

        if ($builder->hasDefinition('nette.latte')) {
            $registerToLatte($builder->getDefinition('nette.latte'));
        }
    }

    /**
     * @param string $class
     * @param string $type
     * @return bool
     */
    private static function isOfType($class, $type)
    {
        return $class === $type || is_subclass_of($class, $type);
    }
}
