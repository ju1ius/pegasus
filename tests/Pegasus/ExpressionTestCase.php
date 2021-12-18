<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarFactory;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Parser\RecursiveDescentParser;
use PHPUnit\Framework\Assert;

class ExpressionTestCase extends PegasusTestCase
{
    protected function assertParseResult(
        mixed $expected,
        Grammar|Expression|array $grammar,
        string $input,
        int $pos = 0
    ) {
        if (!$expected) {
            $this->expectException(ParseError::class);
        }
        $result = self::parse($grammar, $input, $pos);
        if ($expected instanceof Node) {
            PegasusAssert::nodeEquals($expected, $result);
        } else {
            Assert::assertSame($expected, $result);
        }
    }

    protected static function parse(Grammar|Expression|array $grammar, string $text, int $pos = 0): Node|bool
    {
        if ($grammar instanceof Expression) {
            $grammar = GrammarFactory::fromExpression($grammar);
        } else if (is_array($grammar)) {
            $grammar = GrammarFactory::fromArray($grammar);
        }

        return (new RecursiveDescentParser($grammar))->partialParse($text, $pos);
    }
}
