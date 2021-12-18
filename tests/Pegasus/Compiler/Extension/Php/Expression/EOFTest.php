<?php declare(strict_types=1);

namespace Pegasus\Compiler\Extension\Php\Expression;

use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Tests\Compiler\Extension\Php\PhpCompilerTestCase;
use PHPUnit\Framework\Assert;

final class EOFTest extends PhpCompilerTestCase
{
    /**
     * @dataProvider parseProvider
     */
    public function testParse(string $syntax, string $input, int $pos, bool $expected)
    {
        $parser = self::compile($syntax);
        if (!$expected) {
            $this->expectException(ParseError::class);
        }
        $result = $parser->partialParse($input, $pos);
        Assert::assertSame($expected, $result);
    }

    public function parseProvider(): iterable
    {
        yield 'matches on empty input' => [
            'x = EOF',
            '',
            0,
            true,
        ];
        yield 'matches at end of input' => [
            'x = EOF',
            'x',
            1,
            true,
        ];
        yield 'fails if not at end of input' => [
            'x = EOF',
            'x',
            0,
            false,
        ];
    }
}
