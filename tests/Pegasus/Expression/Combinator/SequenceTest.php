<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Combinator;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Composite;
use ju1ius\Pegasus\CST\Node\Decorator;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Tests\ExpressionTestCase;
use ju1ius\Pegasus\Tests\PegasusAssert;
use PHPUnit\Framework\Assert;

class SequenceTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
     *
     * @param Grammar   $expr
     * @param array     $match_args
     * @param Node|true $expected
     */
    public function testMatch(Grammar $expr, array $match_args, $expected)
    {
        if ($expected === true) {
            Assert::assertTrue(self::parse($expr, ...$match_args));
        } else {
            PegasusAssert::nodeEquals(
                $expected,
                self::parse($expr, ...$match_args)
            );
        }
    }
    public function provideTestMatch()
    {
        yield 'With only capturing expressions' => [
            GrammarBuilder::create()->rule('seq')->seq()
                ->literal('foo')
                ->literal('bar')
                ->getGrammar(),
            ['foobar'],
            new Composite('seq', 0, 6, [
                new Terminal('', 0, 3, 'foo'),
                new Terminal('', 3, 6, 'bar'),
            ])
        ];
        yield 'With non-capturing expressions' => [
            GrammarBuilder::create()->rule('seq')->seq()
                ->literal('foo')
                ->ignore()->literal('bar')
                ->literal('baz')
                ->getGrammar(),
            ['foobarbaz'],
            new Composite('seq', 0, 9, [
                new Terminal('', 0, 3, 'foo'),
                new Terminal('', 6, 9, 'baz'),
            ])
        ];
        yield 'With only one capturing expression, lifts the result if it is not a grammar rule.' => [
            GrammarBuilder::create()->rule('seq')->seq()
                ->literal('foo')
                ->ignore()->literal('bar')
                ->getGrammar(),
            ['foobar'],
            new Terminal('seq', 0, 3, 'foo')
        ];
        yield 'With only one capturing expression, decorates the result if it is a grammar rule.' => [
            GrammarBuilder::create()
                ->rule('seq')->seq()
                    ->ref('foo')
                    ->ignore()->literal('bar')
                ->rule('foo')->literal('foo')
                ->getGrammar(),
            ['foobar'],
            new Decorator('seq', 0, 6, new Terminal('foo', 0, 3, 'foo'))
        ];
        yield 'With no capturing expression, returns true' => [
            GrammarBuilder::create()->rule('seq')->seq()
                ->ignore()->literal('foo')
                ->ignore()->literal('bar')
                ->getGrammar(),
            ['foobar'],
            true
        ];
        yield 'Skips a reference to a non-capturing rule' => [
            GrammarBuilder::create()
                ->rule('seq')->sequence()
                    ->literal('a')
                    ->ref('b')
                    ->literal('c')
                ->rule('b')->ignore()->literal('b')
                ->getGrammar(),
            ['abc'],
            new Composite('seq', 0, 3, [
                new Terminal('', 0, 1, 'a'),
                new Terminal('', 2, 3, 'c'),
            ])
        ];
    }

    /**
     * @dataProvider provideTestMatchError
     *
     * @param Grammar $expr
     * @param array   $match_args
     */
    public function testMatchError(Grammar $expr, array $match_args)
    {
        $this->expectException(ParseError::class);
        self::parse($expr, ...$match_args);
    }
    public function provideTestMatchError()
    {
        yield [
            GrammarBuilder::create()->rule('seq')->seq()
                ->literal('foo')
                ->literal('bar')
                ->getGrammar(),
            ['barbaz'],
        ];
    }

}
