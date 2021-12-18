<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarFactory;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Parser\RecursiveDescentParser;

class ExpressionTestCase extends PegasusTestCase
{
    protected function assertParseResult($expected, $grammar, $input, $pos = 0)
    {
        if (!$expected) {
            $this->expectException(ParseError::class);
        }
        $result = $this->parse($grammar, $input, $pos);
        if ($expected instanceof Node) {
            $this->assertNodeEquals($expected, $result);
        } else {
            $this->assertSame($expected, $result);
        }
    }

    protected function parse($grammar, $text, $pos = 0)
    {
        if ($grammar instanceof Expression) {
            $grammar = GrammarFactory::fromExpression($grammar);
        } elseif (is_array($grammar)) {
            $grammar = GrammarFactory::fromArray($grammar);
        } elseif (!$grammar instanceof Grammar) {
            throw new \LogicException('Expected Grammar, Expression or array.');
        }

        $result = (new RecursiveDescentParser($grammar))->partialParse($text, $pos);

        return $result;
    }
}
