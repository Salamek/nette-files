<?php declare(strict_types = 1);

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
namespace Salamek\Files\DI;

use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Salamek\Files\FileStorage;
use Salamek\Files\Filters\Latte;
use Salamek\Files\ImagePipe;


/**
 * Class FilesExtension
 * @package Salamek\Files\DI
 */
class FilesExtension extends CompilerExtension
{
    public function getConfigSchema(): Schema
    {
        return Expect::structure([
            'dataDir' => Expect::string()->required(),
            'wwwTempDir' => Expect::string()->required(),
            'iconDir' => Expect::string()->required(),
        ]);
    }

    public function loadConfiguration()
    {
        $config = (array) $this->getConfig();
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('imagePipe'))
            ->setClass(ImagePipe::class, [$config['dataDir'], $config['wwwTempDir']]);

        $builder->addDefinition($this->prefix('fileStorage'))
            ->setClass(FileStorage::class, [$config['dataDir'], $config['iconDir']]);
        
        $builder->addDefinition($this->prefix('filters'))
            ->setClass(Latte::class)
            ->setAutowired(false);
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();

        $latteFactoryService = $builder->getDefinitionByType(LatteFactory::class)->getResultDefinition();
        $latteFactoryService->addSetup('addFilter', ['request', [$this->prefix('@filters'), 'request']]);
        $latteFactoryService->addSetup('addFilter', ['fileIcon', [$this->prefix('@filters'), 'fileIcon']]);
        $latteFactoryService->addSetup('Salamek\Files\Macros\Latte::install(?->getCompiler())', ['@self']);

    }
}
