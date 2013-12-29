<?php

require_once __DIR__.'/../../Pegasus_TestCase.php';

use ju1ius\Pegasus\Packrat\Parser;
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
        $parser = new Parser($expr);
        return $parser->parse($text, $pos);
    }
    
}
