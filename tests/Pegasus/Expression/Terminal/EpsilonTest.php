<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Terminal;

use ju1ius\Pegasus\Expression\Terminal\Epsilon;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarFactory;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Parser\RecursiveDescentParser;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class EpsilonTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
     *
     * @param string $input
     * @param int    $pos
     * @param bool   $expected
     *
     * @throws Grammar\Exception\AnonymousTopLevelExpression
     */
    public function testMatch($input, $pos, $expected)
    {
        $parser = new RecursiveDescentParser(GrammarFactory::fromArray([
            'test' => new Epsilon()
        ]));
        if (!$expected) {
            $this->expectException(ParseError::class);
        }
        $result = $parser->partialParse($input, $pos);
        $this->assertSame($expected, $result);
        $this->assertSame($pos, $parser->pos, 'Does not consume any input.');
    }

    public function provideTestMatch()
    {
        yield 'Matches when input is empty.' => [
            '', 0, true,
        ];
        yield 'Matches anything without consuming any input.' => [
            'foo', 1, true,
        ];
    }

    public function testMetadata()
    {
        $expr = new Epsilon();
        $this->assertTrue($expr->isCapturingDecidable());
        $this->assertFalse($expr->isCapturing());
    }
}
