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
            'webTempDir' => Expect::string()->required(),
            'webTempPath' => Expect::string()->required(),
            'iconDir' => Expect::string()->required()
        ]);
    }

    public function loadConfiguration()
    {
        $config = (array) $this->getConfig();
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('fileStorage'))
            ->setFactory(FileStorage::class, [$config['dataDir'], $config['iconDir'], $config['webTempDir']]);

        $builder->addDefinition($this->prefix('imagePipe'))
            ->setFactory(ImagePipe::class, [$this->prefix('@fileStorage'),  $config['webTempPath']]);


        
        $builder->addDefinition($this->prefix('filters'))
            ->setFactory(Latte::class)
            ->setAutowired(false);
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();

        $latteFactoryService = $builder->getDefinitionByType(LatteFactory::class)->getResultDefinition();
        $latteFactoryService->addSetup('addFilter', ['imageRequest', [$this->prefix('@filters'), 'imageRequest']]);
        $latteFactoryService->addSetup('addFilter', ['fileIconRequest', [$this->prefix('@filters'), 'fileIconRequest']]);
        $latteFactoryService->addSetup('Salamek\Files\Macros\Latte::install(?->getCompiler())', ['@self']);
    }
}
