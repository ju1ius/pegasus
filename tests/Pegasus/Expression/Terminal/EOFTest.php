<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Terminal;

use ju1ius\Pegasus\Expression\Terminal\EOF;
use ju1ius\Pegasus\GrammarFactory;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Parser\RecursiveDescentParser;
use ju1ius\Pegasus\Tests\ExpressionTestCase;
use PHPUnit\Framework\Assert;

class EOFTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
     */
    public function testMatch(string $input, int $pos, bool $expected)
    {
        $parser = new RecursiveDescentParser(GrammarFactory::fromArray([
            'test' => new EOF()
        ]));
        if (!$expected) {
            $this->expectException(ParseError::class);
        }
        $result = $parser->partialParse($input, $pos);
        Assert::assertSame($expected, $result);
        Assert::assertSame($pos, $parser->pos, 'Does not consume any input.');
    }

    public function provideTestMatch(): iterable
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
        Assert::assertTrue($expr->isCapturingDecidable());
        Assert::assertFalse($expr->isCapturing());
    }
}
