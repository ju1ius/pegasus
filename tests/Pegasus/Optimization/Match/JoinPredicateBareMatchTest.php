<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\Optimization\Match;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\ZeroOrMore;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinPredicateBareMatch;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Tests\Optimization\OptimizationTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class JoinPredicateBareMatchTest extends RegExpOptimizationTestCase
{
    /**
     * @dataProvider provideTestApply
     *
     * @param Grammar    $input
     * @param Expression $expected
     */
    public function testApply(Grammar $input, Expression $expected)
    {
        $optim = $this->createOptimization(JoinPredicateBareMatch::class);
        $ctx = OptimizationContext::of($input);

        $result = $this->applyOptimization($optim, $input, $ctx);
        $this->assertExpressionEquals($expected, $result, 'In capturing context');

        $result = $this->applyOptimization($optim, $input, $ctx->matching());
        $this->assertExpressionEquals($expected, $result, 'In matching context');
    }

    public function provideTestApply()
    {
        yield 'Sequence with a Match before an Assert of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->assert()->match('c')
                ->ref('d')
                ->getGrammar(),
            new Sequence([
                new Reference('a'),
                new Match('(?>b)(?=c)'),
                new Reference('d'),
            ], 'test')
        ];
        yield 'Sequence with a Match before a Not of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->not()->match('c')
                ->ref('d')
                ->getGrammar(),
            new Sequence([
                new Reference('a'),
                new Match('(?>b)(?!c)'),
                new Reference('d'),
            ], 'test')
        ];
        yield 'Sequence with a Match before EOF' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->eof()
                ->ref('c')
                ->getGrammar(),
            new Sequence([
                new Reference('a'),
                new Match('(?>b)\z'),
                new Reference('c'),
            ], 'test')
        ];
        yield 'Sequence with a Match after an Assert of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->assert()->match('b')
                ->match('c')
                ->ref('d')
                ->getGrammar(),
            new Sequence([
                new Reference('a'),
                new Match('(?=b)(?>c)'),
                new Reference('d'),
            ], 'test')
        ];
        yield 'Sequence with a Match after a Not of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->not()->match('b')
                ->match('c')
                ->ref('d')
                ->getGrammar(),
            new Sequence([
                new Reference('a'),
                new Match('(?!b)(?>c)'),
                new Reference('d'),
            ], 'test')
        ];
    }

    /**
     * @dataProvider provideTestAppliesTo
     *
     * @param Grammar $input
     * @param bool    $applies
     */
    public function testAppliesTo(Grammar $input, $applies)
    {
        $optim = $this->createOptimization(JoinPredicateBareMatch::class);
        $expr = $input->getStartExpression();
        $ctx = OptimizationContext::of($input);

        $result = $optim->willPostProcessExpression($expr, $ctx);
        $this->assertSame($applies, $result, 'In capturing context');

        $result = $optim->willPostProcessExpression($expr, $ctx->matching());
        $this->assertSame($applies, $result, 'In matching context');
    }

    public function provideTestAppliesTo()
    {
        yield 'Sequence with a Match before an Assert of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->assert()->match('c')
                ->ref('d')
                ->getGrammar(),
            true
        ];
        yield 'Sequence with a Match before a Not of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->not()->match('c')
                ->ref('d')
                ->getGrammar(),
            true
        ];
        yield 'Sequence with a Match before EOF' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->eof()
                ->ref('c')
                ->getGrammar(),
            true
        ];
        yield 'Sequence with a Match before an Assert of something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->assert()->ref('c')
                ->ref('d')
                ->getGrammar(),
            false
        ];
        yield 'Sequence with a Match before a Not of something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->not()->ref('c')
                ->ref('d')
                ->getGrammar(),
            false
        ];
        yield 'Sequence with a Match after an Assert of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->assert()->match('b')
                ->match('c')
                ->ref('d')
                ->getGrammar(),
            true
        ];
        yield 'Sequence with a Match after a Not of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->not()->match('b')
                ->match('c')
                ->ref('d')
                ->getGrammar(),
            true
        ];
        yield 'Sequence with a Match after an Assert of something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->assert()->ref('b')
                ->match('c')
                ->ref('d')
                ->getGrammar(),
            false
        ];
        yield 'Sequence with a Match after a Not of something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->not()->ref('b')
                ->match('c')
                ->ref('d')
                ->getGrammar(),
            false
        ];
        yield 'Sequence with a Match after a EOF' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->eof()
                ->match('c')
                ->ref('d')
                ->getGrammar(),
            true
        ];
        yield 'Sequence with non-consecutive match & predicate' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->ref('c')
                ->assert()->match('d')
                ->getGrammar(),
            false
        ];
    }
}
