<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Terminal;

use ju1ius\Pegasus\Expression\Terminal\EOF;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Parser\RecursiveDescent;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class EOFTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
     *
     * @param string $input
     * @param int    $pos
     * @param bool   $expected
     */
    public function testMatch($input, $pos, $expected)
    {
        $parser = new RecursiveDescent(Grammar::fromArray([
            'test' => new EOF()
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
        yield 'Matches at the end of the input.' => [
            'foo', 3, true,
        ];
        yield 'Fails if not at end of the input.' => [
            'foo', 0, false,
        ];
    }

    public function testMetadata()
    {
        $expr = new EOF();
        $this->assertTrue($expr->isCapturingDecidable());
        $this->assertFalse($expr->isCapturing());
    }
}
