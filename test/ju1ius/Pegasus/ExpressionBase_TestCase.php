<?php

require_once __DIR__.'/../../Pegasus_TestCase.php';

use ju1ius\Pegasus\Parser\Packrat;
use ju1ius\Pegasus\Expression;


class ExpressionBase_TestCase extends Pegasus_TestCase
{
    protected function expr($class, array $args)
    {
        $class = "ju1ius\Pegasus\Expression\\$class";
        return (new \ReflectionClass($class))->newInstanceArgs($args);
    }

    protected function parse(Expression $expr, $text, $pos=0)
    {
        $parser = new Packrat($expr);
        return $parser->parse($text, $pos);
    }
    
}
