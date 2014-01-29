<?php

namespace ju1ius\Pegasus\Compiler;

use ju1ius\Pegasus\Expression;

use Twig_Extension;
use Twig_Environment;
use Twig_SimpleFilter;
use Twig_SimpleFunction;


class PegasusTwigExtension extends Twig_Extension
{
    protected static $VARID = 0;
    protected $environment = null;

    public function initRuntime(Twig_Environment $env)
    {
        parent::initRuntime($env);
        $this->environment = $env;
    }

    public function getName()
    {
        return 'pegasus';
    }

    public function getGlobals()
    {
        // make macros available globally
        $macros = $this->environment->loadTemplate('macros.twig');
        return [
            'macros' => $macros
        ];   
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('expr_tpl', [$this, 'expr_tpl']),
            new Twig_SimpleFunction('varid', [$this, 'varid'])
        ];
    }

    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('indent', [$this, 'indent'])
        ];
    }

    public function varid()
    {
        return '_' . self::$VARID++;
    }
    
    public function indent($text, $prefix='    ', callable $predicate=null)
    {
        if (null === $predicate) {
            $predicate = 'trim';
        }
        $out = '';
        // split lines while keeping linebreak chars
        foreach (preg_split('/(?<=\r\n|\n|\r)/S', $text) as $line) {
            $out .= $predicate($line) ? $prefix . $line : $line;
        }

        return $out;
    }

    public function expr_tpl(Expression $expr)
    {
        $class = strtolower(str_replace('ju1ius\Pegasus\Expression\\', '', get_class($expr)));
        switch ($class) {
            case 'zeroormore':
            case 'oneormore':
            case 'optional':
                return 'quantifier.twig';
            default:
                return "$class.twig";
        }
    }
}
