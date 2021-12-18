<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Decorator;

use ju1ius\Pegasus\Expression\Decorator\Assert as AssertExpr;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Tests\ExpressionTestCase;
use PHPUnit\Framework\Assert;

class AssertTest extends ExpressionTestCase
{
    public function testMetadata()
    {
        $expr = new AssertExpr();
        Assert::assertTrue($expr->isCapturingDecidable());
        Assert::assertFalse($expr->isCapturing());
        Assert::assertFalse($expr->hasVariableCaptureCount());
    }

    /**
     * @dataProvider provideTestMatch
     */
    public function testMatch(Grammar $expr, array $args, bool $expected)
    {
        Assert::assertSame($expected, self::parse($expr, ...$args));
    }

    public function provideTestMatch(): \Traversable
    {
        yield [
            GrammarBuilder::create()->rule('assert')
                ->assert()->literal('foobar')
                ->getGrammar(),
            ['foobar'],
            true,
        ];
        yield [
            GrammarBuilder::create()->rule('assert')
                ->assert()->literal('bar')
                ->getGrammar(),
            ['foobar', 3],
            true,
        ];
    }

    /**
     * @dataProvider provideTestMatchError
     */
    public function testMatchError(Grammar $expr, array $args)
    {
        $this->expectException(ParseError::class);
        self::parse($expr, ...$args);
    }

    public function provideTestMatchError(): \Traversable
    {
        yield [
            GrammarBuilder::create()->rule('assert')
                ->assert()->literal('foo')
                ->getGrammar(),
            ['barbaz'],
        ];
    }

}
