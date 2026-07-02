<?php declare(strict_types = 1);

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
namespace Salamek\Files\Latte\Nodes;

use Latte\Compiler\Nodes\Php\ArgumentNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\PrintContext;
use Latte\Helpers;


class FileRequestExpressionNode extends ExpressionNode
{
    /**
     * @param ArgumentNode[] $args
     */
    public function __construct(
        private string $filterName,
        public array $args
    ) {
        (function (ArgumentNode ...$args) {})(...$args);
    }

    public function print(PrintContext $context): string
    {
        return '($this->filters->' . $this->filterName . ')(' . $context->implode($this->args) . ')';
    }

    public function &getIterator(): \Generator
    {
        foreach ($this->args as &$arg) {
            yield $arg;
        }
        Helpers::removeNulls($this->args);
    }
}
