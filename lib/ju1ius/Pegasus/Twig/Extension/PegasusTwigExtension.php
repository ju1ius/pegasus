<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Twig\Extension;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Twig\DataCollector;
use ju1ius\Pegasus\Twig\TokenParser\CollectorTokenParser;

use Twig_Extension;
use Twig_Environment;
use Twig_SimpleFilter;
use Twig_SimpleFunction;
use Twig_Error_Loader;


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
        $globals = [
            'data_collector' => $this->collector 
        ];
        try {
            // if a template named macros exists,
            // make it available globally
            $macros = $this->environment->loadTemplate('macros.twig');
            $globals['macros'] = $macros;
        } catch (Twig_Error_Loader $e) {}

        return $globals;   
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
