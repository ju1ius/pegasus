<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Decorator;

use ju1ius\Pegasus\Expression\Decorator\Assert;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class AssertTest extends ExpressionTestCase
{
    public function testMetadata()
    {
        $expr = new Assert();

        $this->assertTrue($expr->isCapturingDecidable());
        $this->assertFalse($expr->isCapturing());
        $this->assertFalse($expr->hasVariableCaptureCount());
    }

    /**
     * @dataProvider provideTestMatch
     *
     * @param Grammar $expr
     * @param array   $args
     * @param bool    $expected
     */
    public function testMatch(Grammar $expr, array $args, $expected)
    {
        $this->assertSame($expected, $this->parse($expr, ...$args));
    }

    public function provideTestMatch()
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
     * @expectedException \ju1ius\Pegasus\Parser\Exception\ParseError
     *
     * @param Grammar $expr
     * @param array   $args
     */
    public function testMatchError(Grammar $expr, array $args)
    {
        $this->parse($expr, ...$args);
    }

    public function provideTestMatchError()
    {
        yield [
            GrammarBuilder::create()->rule('assert')
                ->assert()->literal('foo')
                ->getGrammar(),
            ['barbaz'],
        ];
    }

}
