<?php declare(strict_types=1);
namespace Salamek\Files\Access;
use Salamek\Files\Models\IFile;

final readonly class FileAccess
{
    public function __construct(
        public IFile $file,
        public bool $public = false,
        public ?string $filename = null,
        /** Null permits all configured variants; an array restricts this attachment. */
        public ?array $variants = null,
    ) {}
}
