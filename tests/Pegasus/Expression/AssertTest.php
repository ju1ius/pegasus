<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Transient;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class AssertTest extends ExpressionTestCase
{
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
                Builder::create()->rule('assert')
                    ->assert()->literal('foobar')
                    ->getGrammar(),
                ['foobar'],
                true,
            ],
            [
                Builder::create()->rule('assert')
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
                Builder::create()->rule('assert')
                    ->assert()->literal('foo')
                    ->getGrammar(),
                ['barbaz'],
            ],
        ];
    }

}
