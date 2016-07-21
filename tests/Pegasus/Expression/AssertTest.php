<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Assert;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Node;
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
     * @dataProvider getMatchProvider
     *
     * @param Grammar $expr
     * @param array   $args
     * @param bool    $expected
     */
    public function testMatch(Grammar $expr, array $args, $expected)
    {
        $this->assertSame($expected, $this->parse($expr, ...$args));
    }

    public function getMatchProvider()
    {
        return [
            [
                GrammarBuilder::create()->rule('assert')
                    ->assert()->literal('foobar')
                    ->getGrammar(),
                ['foobar'],
                true,
            ],
            [
                GrammarBuilder::create()->rule('assert')
                    ->assert()->literal('bar')
                    ->getGrammar(),
                ['foobar', 3],
                true,
            ],
        ];
    }

    /**
     * @dataProvider getMatchErrorProvider
     * @expectedException \ju1ius\Pegasus\Parser\Exception\ParseError
     *
     * @param Grammar $expr
     * @param array   $args
     */
    public function testMatchError(Grammar $expr, array $args)
    {
        $this->parse($expr, ...$args);
    }

    public function getMatchErrorProvider()
    {
        return [
            [
                GrammarBuilder::create()->rule('assert')
                    ->assert()->literal('foo')
                    ->getGrammar(),
                ['barbaz'],
            ],
        ];
    }

}
