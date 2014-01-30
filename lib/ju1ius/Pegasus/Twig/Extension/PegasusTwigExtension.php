<?php

namespace ju1ius\Pegasus\Twig\Extension;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Twig\DataCollector;
use ju1ius\Pegasus\Twig\TokenParser\CollectorTokenParser;

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
        $this->collector = new DataCollector();
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
            'macros' => $macros,
            'data_collector' => $this->collector 
        ];   
    }

    public function getTokenParsers()
    {
        return [
            new CollectorTokenParser()
        ];
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('expr_tpl', [$this, 'expr_tpl']),
            new Twig_SimpleFunction('repr', [$this, 'repr'])
        ];
    }

    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('indent', [$this, 'indent'])
        ];
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

    public function repr($value)
    {
        if (null === $value) {
            return 'null'; 
        } elseif (is_int($value) || is_float($value)) {
            return $value;
        } elseif (is_array($value)) {
            $out = '[';
            $first = true;
            foreach ($value as $k => $v) {
                if (!$first) $out .= ', ';
                $first = false;
                $out .= $this->repr($k) . ' => ' . $this->repr($v);
            }
            return $out . ']';
        }
        return sprintf('"%s"', addcslashes($value, "\0\t\"\$\\"));
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
