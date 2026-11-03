<?php declare(strict_types=1);
namespace Salamek\Files\Access;
use Salamek\Files\Models\IFile;

/** A URL description, never an authorization grant. */
final readonly class FileReference
{
    public static function attachment(string $provider, int|string $reference, ?\Salamek\Files\Models\IStructureFile $attachment): ?self
    {
        return $attachment ? new self($provider, (string) $reference, $attachment->getFile()) : null;
    }

    public function __construct(public string $provider, public string $reference, public IFile $file)
    {
        if (!preg_match('/^[a-z][a-z0-9]*(?:[.-][a-z0-9]+)*$/D', $provider)
            || !preg_match('/^[a-zA-Z0-9_-]{1,180}$/D', $reference)
            || !preg_match('/^(?:[a-f0-9]{32}|[a-f0-9]{64})$/D', $file->getSum())) {
            throw new \InvalidArgumentException('Invalid file reference.');
        }
    }
}
