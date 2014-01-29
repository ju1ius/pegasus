<?php

require_once __DIR__.'/../utils.php';
require_once '/home/ju1ius/src/php/3p/Twig-1.15.0/lib/Twig/Autoloader.php';
Twig_Autoloader::register();


use ju1ius\Pegasus\Visitor\GrammarVisitor;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\OneOf;


class PegasusExtension extends Twig_Extension
{
    protected static $VARID = 0;

    public function getName()
    {
        return 'pegasus';
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
    

    public function indent($text, $prefix, callable $predicate=null)
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

$loader = new Twig_Loader_Filesystem(__DIR__.'/tpl');
$twig = new Twig_Environment($loader, [
    'autoescape' => false
]);
$twig->addExtension(new PegasusExtension);

$g = new Grammar();
$g['seq'] = new Sequence([
    new Literal('foo'),
    new Literal('bar')
]);
$g['choice'] = new OneOf([
    new Literal('foo'),
    new Literal('bar')
]);
$g['choice2'] = new OneOf([
    new Sequence([
        new Literal('foo'),
        new Literal('bar')
    ]),
    new Literal('baz')
]);
$tpl = $twig->loadTemplate('parser.twig');
$parser = $tpl->render([
    'namespace' => 'Foo\Bar',
    'base_class' => 'Packrat',
    'class' => 'MyParser',
    'grammar' => $g,
]);

echo $parser;
