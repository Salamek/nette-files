<?php declare(strict_types = 1);

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
namespace Salamek\Files\Latte\Nodes;

use Latte\CompileException;
use Latte\Compiler\Nodes\Html\ExpressionAttributeNode;
use Latte\Compiler\Nodes\Php\ModifierNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;


class FileRequestNode extends StatementNode
{
    public FileRequestExpressionNode $expression;
    public ModifierNode $modifier;

    public static function create(Tag $tag, string $filterName): self|ExpressionAttributeNode
    {
        $tag->expectArguments('filename');
        $args = $tag->parser->parseArguments()->toArguments();

        if ($args === []) {
            throw new CompileException('Please provide filename.', $tag->position);
        }

        $expression = new FileRequestExpressionNode($filterName, $args);

        if ($tag->isNAttribute()) {
            return new ExpressionAttributeNode('src', $expression, new ModifierNode([]), ' ', position: $tag->position);
        }

        $node = new self;
        $node->expression = $expression;
        $node->modifier = $tag->parser->parseModifier();
        $node->modifier->escape = !$node->modifier->removeFilter('noescape');
        return $node;
    }

    public function print(PrintContext $context): string
    {
        return $context->format(
            "echo %modify(%node) %line;\n",
            $this->modifier,
            $this->expression,
            $this->position,
        );
    }

    public function &getIterator(): \Generator
    {
        yield $this->expression;
        yield $this->modifier;
    }
}
