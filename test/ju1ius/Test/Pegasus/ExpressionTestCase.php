<?php

namespace ju1ius\Test\Pegasus;

use ju1ius\Test\Pegasus\PegasusTestCase;

use ju1ius\Pegasus\Parser\Packrat;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;


class ExpressionTestCase extends PegasusTestCase
{
    protected function expr($class, array $args)
    {
        $class = "ju1ius\Pegasus\Expression\\$class";
        return (new \ReflectionClass($class))->newInstanceArgs($args);
    }

    protected function parse(Expression $expr, $text, $pos=0)
    {
		$name = $expr->name ?: $expr->id;
		$g = new Grammar([$name => $expr], $expr);
        $parser = new Packrat($g);
        return $parser->parse($text, $pos);
    }
}
