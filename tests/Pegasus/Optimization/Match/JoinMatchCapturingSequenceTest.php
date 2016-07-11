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
use ju1ius\Pegasus\Expression\GroupMatch;
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\Skip;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Optimization\Match\JoinMatchCapturingSequence;
use ju1ius\Pegasus\Optimization\OptimizationContext;
use ju1ius\Pegasus\Tests\Optimization\OptimizationTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class JoinMatchCapturingSequenceTest extends OptimizationTestCase
{
    /**
     * @dataProvider getApplyProvider
     *
     * @param Grammar    $input
     * @param Expression $expected
     */
    public function testApply(Grammar $input, Expression $expected)
    {
        $result = $this->applyOptimization(new JoinMatchCapturingSequence(), $input);
        $this->assertExpressionEquals($expected, $result);
    }

    public function getApplyProvider()
    {
        return [
            'A ssequence of only skipping matches' => [
                Builder::create()->rule('test')->sequence()
                    ->skip()->match('a')
                    ->skip()->match('b')
                    ->skip()->match('c')
                ->getGrammar(),
                new Skip(new Match('(?>a)(?>b)(?>c)'), 'test')
            ],
            'A sequence of skipping matches before something else' => [
                Builder::create()->rule('test')->sequence()
                    ->skip()->match('a')
                    ->skip()->match('b')
                    ->ref('c')
                    ->getGrammar(),
                new Sequence([
                    new Skip(new Match('(?>a)(?>b)')),
                    new Reference('c'),
                ], 'test')
            ],
            'A sequence of skipping matches after something else' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->skip()->match('b')
                    ->skip()->match('c')
                    ->getGrammar(),
                new Sequence([
                    new Reference('a'),
                    new Skip(new Match('(?>b)(?>c)')),
                ], 'test')
            ],
            'A sequence of only matches' => [
                Builder::create()->rule('test')->sequence()
                    ->match('a')
                    ->match('b')
                    ->match('c')
                    ->getGrammar(),
                new GroupMatch(new Match('(a)(b)(c)'), 3, 'test')
            ],
            'A sequence of only group matches' => [
                Grammar::fromArray([
                    'test' => new Sequence([
                        new GroupMatch(new Match('\s*(a)'), 1),
                        new GroupMatch(new Match('\s*(b)'), 1),
                        new GroupMatch(new Match('\s*(c)'), 1),
                    ])
                ]),
                new GroupMatch(new Match('(?>\s*(a))(?>\s*(b))(?>\s*(c))'), 3, 'test')
            ],
            'A mix of matches and single-group group matches' => [
                Grammar::fromArray([
                    'test' => new Sequence([
                        new GroupMatch(new Match('\s*(a)'), 1),
                        new Match('[+-]'),
                        new GroupMatch(new Match('\s*(b)'), 1),
                    ])
                ]),
                new GroupMatch(new Match('(?>\s*(a))([+-])(?>\s*(b))'), 3, 'test')
            ]
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
        $ctx = OptimizationContext::create($input);
        $result = (new JoinMatchCapturingSequence)->appliesTo($input->getStartRule(), $ctx);
        $this->assertSame($applies, $result);
    }

    public function getAppliesToProvider()
    {
        return [
            'Sequence of matches before something non-capturing' => [
                Builder::create()->rule('test')->sequence()
                    ->match('a')
                    ->match('b')
                    ->skip()->ref('c')
                    ->getGrammar(),
                true
            ],
            'Sequence of matches before something capturing' => [
                Builder::create()->rule('test')->sequence()
                    ->match('a')
                    ->match('b')
                    ->ref('c')
                    ->getGrammar(),
                false
            ],
            'Sequence of matches after something non-capturing' => [
                Builder::create()->rule('test')->sequence()
                    ->skip()->ref('a')
                    ->match('b')
                    ->match('c')
                    ->getGrammar(),
                true
            ],
            'Sequence of matches after something capturing' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->match('b')
                    ->match('c')
                    ->getGrammar(),
                false
            ],
            'Sequence of skipping matches before something else' => [
                Builder::create()->rule('test')->sequence()
                    ->skip()->match('a')
                    ->skip()->match('b')
                    ->ref('c')
                    ->getGrammar(),
                true
            ],
            'Sequence of skipping matches after something else' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->skip()->match('b')
                    ->skip()->match('c')
                    ->getGrammar(),
                true
            ],
            'Sequence of single-group group matches before sthg non-capturing' => [
                Grammar::fromArray([
                    'test' => new Sequence([
                        new GroupMatch(new Match('a'), 1),
                        new GroupMatch(new Match('b'), 1),
                        new Skip(new Reference('c')),
                    ], 'test')
                ]),
                true
            ],
            'Sequence of single-group group matches before sthg capturing' => [
                Grammar::fromArray([
                    'test' => new Sequence([
                        new GroupMatch(new Match('a'), 1),
                        new GroupMatch(new Match('b'), 1),
                        new Reference('c'),
                    ], 'test')
                ]),
                false
            ],
            'Sequence of single-group group matches after sthg non-capturing' => [
                Grammar::fromArray([
                    'test' => new Sequence([
                        new Skip(new Reference('a')),
                        new GroupMatch(new Match('b'), 1),
                        new GroupMatch(new Match('c'), 1),
                    ], 'test')
                ]),
                true
            ],
            'Sequence of single-group group matches after sthg capturing' => [
                Grammar::fromArray([
                    'test' => new Sequence([
                        new Reference('a'),
                        new GroupMatch(new Match('b'), 1),
                        new GroupMatch(new Match('c'), 1),
                    ], 'test')
                ]),
                false
            ],
            'Sequence of multi-group group matches before sthg else' => [
                Grammar::fromArray([
                    'test' => new Sequence([
                        new GroupMatch(new Match('(a)(b)'), 2),
                        new GroupMatch(new Match('(c)(d)'), 2),
                        new Reference('e'),
                    ], 'test')
                ]),
                false
            ],
            'Sequence of multi-group group matches after sthg else' => [
                Grammar::fromArray([
                    'test' => new Sequence([
                        new Reference('a'),
                        new GroupMatch(new Match('(b)(c)'), 2),
                        new GroupMatch(new Match('(d)(e)'), 2),
                    ], 'test')
                ]),
                false
            ],
            'Sequence with only one match' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->skip()->match('b')
                    ->ref('c')
                    ->getGrammar(),
                false
            ],
            'Sequence of non-consecutive matches' => [
                Builder::create()->rule('test')->sequence()
                    ->skip()->match('a')
                    ->ref('b')
                    ->skip()->match('c')
                    ->getGrammar(),
                false
            ],
            'A non-sequence' => [
                Builder::create()->rule('test')->oneOf()
                    ->skip()->match('a')
                    ->skip()->match('b')
                    ->skip()->match('c')
                    ->getGrammar(),
                false
            ],
        ];
    }
}
