<?php declare(strict_types = 1);

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
namespace Salamek\Files\DI;

use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Salamek\Files\FileIconPipe;
use Salamek\Files\FileStorage;
use Salamek\Files\Filters\Latte;
use Salamek\Files\ImagePipe;
use Salamek\Files\Latte\FilesLatteExtension;


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
            'iconPath' => Expect::string('assets/file/ico'),
            'variants' => Expect::array()->default([]),
            'publicMaxAge' => Expect::int(3600)->min(0),
            'accelMapping' => Expect::arrayOf('string')->default([]),
            'iconDir' => Expect::string()->required()
        ]);
    }

    public function loadConfiguration()
    {
        $config = (array) $this->getConfig();
        $builder = $this->getContainerBuilder();
        if (isset($builder->parameters['wwwDir'])) {
            $canonical = static function (string $path): string {
                $path = \Nette\Utils\FileSystem::normalizePath($path); $suffix = [];
                while (!file_exists($path) && !is_link($path) && dirname($path) !== $path) { array_unshift($suffix, basename($path)); $path = dirname($path); }
                $resolved = realpath($path);
                if ($resolved === false) { throw new \LogicException('Cannot resolve storage root.'); }
                return rtrim($resolved, '/') . ($suffix ? '/' . implode('/', $suffix) : '');
            };
            $webRoot = $canonical($builder->parameters['wwwDir']);
            foreach (['dataDir', 'webTempDir'] as $key) {
                $path = $canonical($config[$key]);
                if ($path === $webRoot || str_starts_with($path, $webRoot . '/')) {
                    throw new \LogicException('Uploaded files and generated previews must be outside www: ' . $key);
                }
            }
        }


        $builder->addDefinition($this->prefix('fileStorage'))
            ->setFactory(FileStorage::class, [$config['dataDir'], $config['iconDir'], $config['webTempDir']]);

        $builder->addDefinition($this->prefix('providers'))->setFactory(\Salamek\Files\Access\ProviderRegistry::class);
        $builder->addDefinition($this->prefix('variants'))->setFactory(\Salamek\Files\Access\VariantRegistry::class, [$config['variants']]);
        $builder->addDefinition($this->prefix('urls'))->setFactory(\Salamek\Files\Access\FileUrl::class);
        $builder->addDefinition($this->prefix('bodyFiles'))->setFactory(\Salamek\Files\Access\BodyFiles::class);
        $builder->addDefinition($this->prefix('delivery'))->setFactory(\Salamek\Files\Access\FileDelivery::class, [
            'publicMaxAge' => $config['publicMaxAge'], 'accelMapping' => $config['accelMapping'],
        ]);
        $builder->addDefinition($this->prefix('presenter'))->setFactory(\Salamek\Files\Access\FilePresenter::class);

        $builder->addDefinition($this->prefix('imagePipe'))
            ->setFactory(ImagePipe::class);

        $builder->addDefinition($this->prefix('fileIconPipe'))
            ->setFactory(FileIconPipe::class, ['iconPath' => $config['iconPath']]);

        
        $builder->addDefinition($this->prefix('filters'))
            ->setFactory(Latte::class)
            ->setAutowired(false);

        $builder->addDefinition($this->prefix('latteExtension'))
            ->setFactory(FilesLatteExtension::class, [$this->prefix('@filters')])
            ->setAutowired(false);
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();

        $providers = []; $identifiers = [];
        foreach ($builder->findByType(\Salamek\Files\Access\IFileAccessProvider::class) as $name => $definition) {
            $id = $definition->getType()::getId();
            if (!preg_match('/^[a-z][a-z0-9]*(?:[.-][a-z0-9]+)*$/D', $id) || isset($identifiers[$id])) {
                throw new \LogicException('Invalid or duplicate file resolver: ' . $id);
            }
            $identifiers[$id] = true; $providers[] = '@' . $name;
        }
        $builder->getDefinition($this->prefix('providers'))->setArgument('providers', $providers);

        $latteFactoryService = $builder->getDefinitionByType(LatteFactory::class)->getResultDefinition();
        $latteFactoryService->addSetup('addExtension', [$this->prefix('@latteExtension')]);
    }
}
