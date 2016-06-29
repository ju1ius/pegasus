<?php

namespace ju1ius\Pegasus\Tests;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Parser\RecursiveDescent;

class ExpressionTestCase extends PegasusTestCase
{
    protected function expr($class, array $args)
    {
        $class = "ju1ius\\Pegasus\\Expression\\{$class}";
        return (new \ReflectionClass($class))->newInstanceArgs($args);
    }

    protected function parse(Expression $expr, $text, $pos=0)
    {
		$name = $expr->name ?: $expr->id;
        $g = Grammar::fromArray([$name => $expr]);
        $result = (new RecursiveDescent($g))->parse($text, $pos);
        // unset Node->expr so we can test it easily
        $result->expr = null;
        return $result;
    }
}
