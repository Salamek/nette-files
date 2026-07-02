<?php declare(strict_types = 1);

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
namespace Salamek\Files\Latte;

use Latte\Extension;
use Salamek\Files\Filters\Latte as FilesLatteFilters;
use Salamek\Files\Latte\Nodes\FileRequestNode;


class FilesLatteExtension extends Extension
{
    public function __construct(
        private FilesLatteFilters $filters
    ) {
    }

    public function getFilters(): array
    {
        return [
            'imageRequest' => [$this->filters, 'imageRequest'],
            'fileIconRequest' => [$this->filters, 'fileIconRequest'],
        ];
    }

    public function getTags(): array
    {
        return [
            'img' => fn($tag) => FileRequestNode::create($tag, 'imageRequest'),
            'n:img' => fn($tag) => FileRequestNode::create($tag, 'imageRequest'),
            'fileIcon' => fn($tag) => FileRequestNode::create($tag, 'fileIconRequest'),
            'n:fileIcon' => fn($tag) => FileRequestNode::create($tag, 'fileIconRequest'),
        ];
    }
}
