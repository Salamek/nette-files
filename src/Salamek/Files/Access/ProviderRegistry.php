<?php declare(strict_types=1);
namespace Salamek\Files\Access;

final class ProviderRegistry
{
    /** @var array<string, IFileAccessProvider> */
    private array $providers = [];

    /** @param IFileAccessProvider[] $providers */
    public function __construct(array $providers = [])
    {
        foreach ($providers as $provider) {
            $id = $provider::getId();
            if (isset($this->providers[$id])) { throw new \LogicException('Duplicate file resolver: ' . $id); }
            $this->providers[$id] = $provider;
        }
    }

    public function resolve(string $provider, string $reference): ?FileAccess
    {
        return isset($this->providers[$provider]) ? $this->providers[$provider]->resolve($reference) : null;
    }
}
