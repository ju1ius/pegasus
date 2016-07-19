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
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinMatchMatchingSequence;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Tests\Optimization\OptimizationTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class JoinMatchMatchingSequenceTest extends OptimizationTestCase
{
    /**
     * @dataProvider getApplyProvider
     *
     * @param Grammar    $input
     * @param Expression $expected
     */
    public function testApply(Grammar $input, Expression $expected)
    {
        $ctx = OptimizationContext::of($input, OptimizationContext::TYPE_MATCHING);
        $result = $this->applyOptimization(new JoinMatchMatchingSequence(), $input, $ctx);
        $this->assertExpressionEquals($expected, $result);
    }

    public function getApplyProvider()
    {
        return [
            'Joins a sequence of only matches' => [
                Builder::create()->rule('test')->sequence()
                    ->match('a')
                    ->match('b')
                    ->match('c')
                    ->getGrammar(),
                new Match('(?>a)(?>b)(?>c)', [], 'test')
            ],
            'Joins a sequence of only matches with flags' => [
                Builder::create()->rule('test')->sequence()
                    ->match('a', ['i'])
                    ->match('b', ['um'])
                    ->match('c')
                    ->getGrammar(),
                new Match('(?>(?i)a)(?>(?um)b)(?>c)', [], 'test')
            ],
            'Joins a sequence of literals' => [
                Builder::create()->rule('test')->sequence()
                    ->literal('a/b')
                    ->literal('c?')
                    ->literal('{d}')
                    ->getGrammar(),
                new Match('(?>a\/b)(?>c\?)(?>\{d\})', [], 'test')
            ],
            'Joins a sequence of literals or matches' => [
                Builder::create()->rule('test')->sequence()
                    ->literal('foo/bar')
                    ->match('c+|d')
                    ->literal('baz')
                    ->getGrammar(),
                new Match('(?>foo\/bar)(?>c+|d)(?>baz)', [], 'test')
            ],
            'Joins consecutive matches before something else' => [
                Builder::create()->rule('test')->sequence()
                    ->match('a', ['i'])
                    ->match('b')
                    ->ref('c')
                    ->getGrammar(),
                new Sequence([
                    new Match('(?>(?i)a)(?>b)'),
                    new Reference('c'),
                ], 'test')
            ],
            'Joins consecutive matches after something else' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('c')
                    ->match('a', ['i'])
                    ->match('b')
                    ->getGrammar(),
                new Sequence([
                    new Reference('c'),
                    new Match('(?>(?i)a)(?>b)'),
                ], 'test')
            ],
        ];
    }

    /**
     * @param Grammar $input
     * @param         $context
     * @param bool    $applies
     *
     * @throws Grammar\Exception\MissingStartRule
     * @dataProvider getAppliesToProvider
     */
    public function testAppliesTo(Grammar $input, $context, $applies)
    {
        $ctx = OptimizationContext::of($input, $context);
        $result = (new JoinMatchMatchingSequence)->appliesTo($input->getStartExpression(), $ctx);
        $this->assertSame($applies, $result);
    }

    public function getAppliesToProvider()
    {
        return [
            'A sequence of matches in a matching context' => [
                Builder::create()->rule('test')->sequence()
                    ->match('a')
                    ->literal('b')
                    ->match('c')
                    ->getGrammar(),
                OptimizationContext::TYPE_MATCHING,
                true
            ],
            'A sequence of matches in a capturing context' => [
                Builder::create()->rule('test')->sequence()
                    ->match('a')
                    ->literal('b')
                    ->match('c')
                    ->getGrammar(),
                OptimizationContext::TYPE_CAPTURING,
                false
            ],
            'Consecutive matches before something else' => [
                Builder::create()->rule('test')->sequence()
                    ->match('a', ['i'])
                    ->match('b')
                    ->ref('c')
                    ->getGrammar(),
                OptimizationContext::TYPE_MATCHING,
                true
            ],
            'Consecutive matches after something else' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('c')
                    ->match('a', ['i'])
                    ->match('b')
                    ->getGrammar(),
                OptimizationContext::TYPE_MATCHING,
                true
            ],
            'A sequence with only one match' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->match('b')
                    ->ref('c')
                ->getGrammar(),
                OptimizationContext::TYPE_MATCHING,
                false
            ],
            'Non-consecutive matches' => [
                Builder::create()->rule('test')->sequence()
                    ->match('a')
                    ->ref('b')
                    ->match('c')
                    ->getGrammar(),
                OptimizationContext::TYPE_MATCHING,
                false
            ],
            'A non sequence' => [
                Builder::create()->rule('test')->oneOf()
                    ->match('a')
                    ->match('b')
                    ->match('c')
                    ->getGrammar(),
                OptimizationContext::TYPE_MATCHING,
                false
            ]
        ];
    }
}
