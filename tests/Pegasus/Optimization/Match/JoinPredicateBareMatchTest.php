<?php
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
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinPredicateBareMatch;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Tests\Optimization\OptimizationTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class JoinPredicateBareMatchTest extends OptimizationTestCase
{
    /**
     * @dataProvider getApplyProvider
     *
     * @param Grammar    $input
     * @param Expression $expected
     */
    public function testApply(Grammar $input, Expression $expected)
    {
        $optim = new JoinPredicateBareMatch();
        $ctx = OptimizationContext::create($input);

        $result = $this->applyOptimization($optim, $input, $ctx);
        $this->assertExpressionEquals($expected, $result, 'In capturing context');

        $result = $this->applyOptimization($optim, $input, $ctx->matching());
        $this->assertExpressionEquals($expected, $result, 'In matching context');
    }

    public function getApplyProvider()
    {
        return [
            'Sequence with a Match before an Assert of a Match' => [
                Builder::create()->rule('test')->sequence()
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
            ],
            'Sequence with a Match before a Not of a Match' => [
                Builder::create()->rule('test')->sequence()
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
            ],
            'Sequence with a Match before EOF' => [
                Builder::create()->rule('test')->sequence()
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
            ],
            'Sequence with a Match after an Assert of a Match' => [
                Builder::create()->rule('test')->sequence()
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
            ],
            'Sequence with a Match after a Not of a Match' => [
                Builder::create()->rule('test')->sequence()
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
            ],
        ];
    }

    /**
     * @dataProvider getAppliesToProvider
     *
     * @param Grammar $input
     * @param bool    $applies
     */
    public function testAppliesTo(Grammar $input, $applies)
    {
        $optim = new JoinPredicateBareMatch();
        $expr = $input->getStartRule();
        $ctx = OptimizationContext::create($input);

        $result = $optim->appliesTo($expr, $ctx);
        $this->assertSame($applies, $result, 'In capturing context');

        $result = $optim->appliesTo($expr, $ctx->matching());
        $this->assertSame($applies, $result, 'In matching context');
    }

    public function getAppliesToProvider()
    {
        return [
            'Sequence with a Match before an Assert of a Match' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->match('b')
                    ->assert()->match('c')
                    ->ref('d')
                    ->getGrammar(),
                true
            ],
            'Sequence with a Match before a Not of a Match' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->match('b')
                    ->not()->match('c')
                    ->ref('d')
                    ->getGrammar(),
                true
            ],
            'Sequence with a Match before EOF' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->match('b')
                    ->eof()
                    ->ref('c')
                    ->getGrammar(),
                true
            ],
            'Sequence with a Match before an Assert of something else' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->match('b')
                    ->assert()->ref('c')
                    ->ref('d')
                    ->getGrammar(),
                false
            ],
            'Sequence with a Match before a Not of something else' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->match('b')
                    ->not()->ref('c')
                    ->ref('d')
                    ->getGrammar(),
                false
            ],
            'Sequence with a Match after an Assert of a Match' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->assert()->match('b')
                    ->match('c')
                    ->ref('d')
                    ->getGrammar(),
                true
            ],
            'Sequence with a Match after a Not of a Match' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->not()->match('b')
                    ->match('c')
                    ->ref('d')
                    ->getGrammar(),
                true
            ],
            'Sequence with a Match after an Assert of something else' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->assert()->ref('b')
                    ->match('c')
                    ->ref('d')
                    ->getGrammar(),
                false
            ],
            'Sequence with a Match after a Not of something else' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->not()->ref('b')
                    ->match('c')
                    ->ref('d')
                    ->getGrammar(),
                false
            ],
            'Sequence with a Match after a EOF' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->eof()
                    ->match('c')
                    ->ref('d')
                    ->getGrammar(),
                true
            ],
            'Sequence with non-consecutive match & predicate' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->match('b')
                    ->ref('c')
                    ->assert()->match('d')
                    ->getGrammar(),
                false
            ],
        ];
    }
}
