<?php declare(strict_types = 1);

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
namespace Salamek\Files\Macros;

use Latte\Compiler;
use Latte\MacroNode;
use Latte\PhpWriter;
use Latte\Macros\MacroSet;


/**
 * Class Latte
 * @package Salamek\Files\Macros
 */
class Latte extends MacroSet
{

    /**
     * @param Compiler $compiler
     * @return static
     */
    public static function install(Compiler $compiler)
    {
        $me = new static($compiler);

        /**
         * {img [namespace/]$name[, $size[, $flags]]}
         */
        $me->addMacro('img', [$me, 'macroImg'], null, [$me, 'macroAttrImg']);

        return $me;
    }


    /**
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @return string
     * @throws \Latte\CompileException
     */
    public function macroImg(MacroNode $node, PhpWriter $writer)
    {
        $arguments = Helpers::prepareMacroArguments($node->args);
        if ($arguments["name"] === null) {
            throw new \InvalidArgumentException("Please provide filename.");
        }

        $arguments = array_map(function ($value) use ($writer) {
            return $value ? $writer->formatWord($value) : 'NULL';
        }, $arguments);

        return $writer->write('echo %modify(call_user_func($this->filters->request, ' . implode(", ", $arguments) . '))');
    }


    /**
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @return string
     * @throws \Latte\CompileException
     */
    public function macroAttrImg(MacroNode $node, PhpWriter $writer)
    {
        $arguments = Helpers::prepareMacroArguments($node->args);
        if ($arguments["name"] === null) {
            throw new \InvalidArgumentException("Please provide filename.");
        }

        $arguments = array_map(function ($value) use ($writer) {
            return $value ? $writer->formatWord($value) : 'NULL';
        }, $arguments);

        return $writer->write('?> src="<?php declare(strict_types = 1); echo  %modify(call_user_func($this->filters->request, ' . implode(", ", $arguments) . '))?>" <?php declare(strict_types = 1);');
    }
}
