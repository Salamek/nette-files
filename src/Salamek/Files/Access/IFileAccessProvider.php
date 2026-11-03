<?php declare(strict_types=1);
namespace Salamek\Files\Access;

interface IFileAccessProvider
{
    /** Globally unique resolver identifier, e.g. gallery.picture. */
    public static function getId(): string;

    /** Resolve the attachment AND authorize the current visitor. Null means denied/not found. */
    public function resolve(string $reference): ?FileAccess;
}
