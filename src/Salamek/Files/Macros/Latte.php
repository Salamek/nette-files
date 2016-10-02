<?php

namespace Salamek\Files\Macros;

use Salamek\Files\ImagePipe;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\PhpWriter;
use Latte\Runtime\Template;
use Latte\Macros\MacroSet;
use Nette;
use Tracy\Debugger;


/**
 * Class Latte
 * @package Salamek\Files\Macros
 */
class Latte extends MacroSet
{

    /**
     * @var bool
     */
    private $isUsed = false;


    /**
     * @param \Nette\Latte\Compiler $compiler
     *
     * @return ImgMacro|MacroSet
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
     * @param Nette\Latte\MacroNode $node
     * @param Nette\Latte\PhpWriter $writer
     * @return string
     * @throws Nette\Latte\CompileException
     */
    public function macroImg(MacroNode $node, PhpWriter $writer)
    {
        $this->isUsed = true;
        $arguments = Helpers::prepareMacroArguments($node->args);
        if ($arguments["name"] === null) {
            throw new Nette\Latte\CompileException("Please provide filename.");
        }

        $arguments = array_map(function ($value) use ($writer) {
            return $value ? $writer->formatWord($value) : 'NULL';
        }, $arguments);

        //$command = '\Tracy\Debugger::barDump($this->filters->imagePipe);';
        /*$command = '$this->filters->imagePipe';
        $command .= '->request(' . implode(", ", $arguments) . ')';*/

        //return $writer->write('echo %modify(call_user_func($this->filters->request, %node.word))');
        return $writer->write('echo %modify(call_user_func($this->filters->request, ' . implode(", ", $arguments) . '))');

        return $writer->write('echo %escape(' . $writer->formatWord($command) . ')');
    }


    /**
     * @param Nette\Latte\MacroNode $node
     * @param Nette\Latte\PhpWriter $writer
     * @return string
     * @throws Nette\Latte\CompileException
     */
    public function macroAttrImg(MacroNode $node, PhpWriter $writer)
    {
        $this->isUsed = true;
        $arguments = Helpers::prepareMacroArguments($node->args);
        if ($arguments["name"] === null) {
            throw new Nette\Latte\CompileException("Please provide filename.");
        }

        $arguments = array_map(function ($value) use ($writer) {
            return $value ? $writer->formatWord($value) : 'NULL';
        }, $arguments);


        $command = '\Tracy\Debugger::barDump($this->filters->imagePipe);';
        //$command = '$this->filters->imagePipe';
        //$command .= '->request(' . implode(", ", $arguments) . ')';

        return $writer->write('?> src="<?php echo %escape(' . $writer->formatWord($command) . ')?>" <?php');
    }


    /**
     */
    public function initialize()
    {
        $this->isUsed = false;
    }


    /**
     * Finishes template parsing.
     *
     * @return array(prolog, epilog)
     */
    public function finalize()
    {
        if (!$this->isUsed) {
            return [];
        }

        return array(
            get_called_class() . '::validateTemplateParams($template);',
            null
        );
    }


    /**
     * @param Template $template
     * @throws Nette\InvalidStateException
     */
    public static function validateTemplateParams(Template $template)
    {
        $params = $template->getParameter('template');
        if (!property_exists($params->global, 'imagePipe') || !$params->global->imagePipe instanceof ImagePipe) {
            throw new Nette\InvalidStateException('ImagePipe was not found in template filters');
        }
    }

}
