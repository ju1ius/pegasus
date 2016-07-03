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

    protected function parse($grammar, $text, $pos = 0)
    {
        if ($grammar instanceof Expression) {
            $name = $grammar->name ?: $grammar->id;
            $grammar = Grammar::fromArray([$name => $grammar]);
        } elseif (is_array($grammar)) {
            $grammar = Grammar::fromArray($grammar);
        } elseif (!$grammar instanceof Grammar) {
            throw new \LogicException('Expected Grammar, Expression or array.');
        }

        $result = (new RecursiveDescent($grammar))->parse($text, $pos);
        // unset Node->expr so we can test it easily
        $result->expr = null;

        return $result;
    }
}
