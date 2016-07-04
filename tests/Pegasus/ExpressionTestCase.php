<?php

namespace ju1ius\Pegasus\Tests;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Parser\RecursiveDescent;

class ExpressionTestCase extends PegasusTestCase
{
    protected function parse($grammar, $text, $pos = 0)
    {
        if ($grammar instanceof Expression) {
            $grammar = Grammar::fromExpression($grammar);
        } elseif (is_array($grammar)) {
            $grammar = Grammar::fromArray($grammar);
        } elseif (!$grammar instanceof Grammar) {
            throw new \LogicException('Expected Grammar, Expression or array.');
        }

        $result = (new RecursiveDescent($grammar))->parse($text, $pos);

        return $result;
    }
}
