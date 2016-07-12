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
use ju1ius\Pegasus\Expression\Skip;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Optimization\Match\JoinPredicateNestedMatch;
use ju1ius\Pegasus\Optimization\OptimizationContext;
use ju1ius\Pegasus\Tests\Optimization\OptimizationTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class JoinPredicateNestedMatchTest extends OptimizationTestCase
{
    /**
     * @dataProvider getApplyProvider
     *
     * @param Grammar    $input
     * @param Expression $expected
     */
    public function testApply(Grammar $input, Expression $expected)
    {
        $optim = new JoinPredicateNestedMatch();
        $ctx = OptimizationContext::create($input);

        $result = $this->applyOptimization($optim, $input, $ctx);
        $this->assertExpressionEquals($expected, $result, 'In capturing context');

        $result = $this->applyOptimization($optim, $input, $ctx->matching());
        $this->assertExpressionEquals($expected, $result, 'In matching context');
    }

    public function getApplyProvider()
    {
        return [
            'Sequence with a Skipped Match before an Assert of a Match' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->skip()->match('b')
                    ->assert()->match('c')
                    ->ref('d')
                    ->getGrammar(),
                new Sequence([
                    new Reference('a'),
                    new Skip(new Match('(?>b)(?=c)')),
                    new Reference('d'),
                ], 'test')
            ],
            'Sequence with a Skipped Match before a Not of a Match' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->skip()->match('b')
                    ->not()->match('c')
                    ->ref('d')
                    ->getGrammar(),
                new Sequence([
                    new Reference('a'),
                    new Skip(new Match('(?>b)(?!c)')),
                    new Reference('d'),
                ], 'test')
            ],
            'Sequence with a Skipped Match before EOF' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->skip()->match('b')
                    ->eof()
                    ->ref('d')
                    ->getGrammar(),
                new Sequence([
                    new Reference('a'),
                    new Skip(new Match('(?>b)\z')),
                    new Reference('d'),
                ], 'test')
            ],
            'Sequence with a Skipped Match after an Assert of a Match' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->assert()->match('b')
                    ->skip()->match('c')
                    ->ref('d')
                    ->getGrammar(),
                new Sequence([
                    new Reference('a'),
                    new Skip(new Match('(?=b)(?>c)')),
                    new Reference('d'),
                ], 'test')
            ],
            'Sequence with a Skipped Match after a Not of a Match' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->not()->match('b')
                    ->skip()->match('c')
                    ->ref('d')
                    ->getGrammar(),
                new Sequence([
                    new Reference('a'),
                    new Skip(new Match('(?!b)(?>c)')),
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
        $optim = new JoinPredicateNestedMatch();
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
            'Sequence with a Skipped Match before an Assert of a Match' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->skip()->match('b')
                    ->assert()->match('c')
                    ->ref('d')
                    ->getGrammar(),
                true
            ],
            'Sequence with a Skipped Match before a Not of a Match' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->skip()->match('b')
                    ->not()->match('c')
                    ->ref('d')
                    ->getGrammar(),
                true
            ],
            'Sequence with a Skipped Match before EOF' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->skip()->match('b')
                    ->eof()
                    ->ref('d')
                    ->getGrammar(),
                true
            ],
            'Sequence with a Skipped Match before an Assert of something else' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->skip()->match('b')
                    ->assert()->ref('c')
                    ->getGrammar(),
                false
            ],
            'Sequence with a Skipped Match before a Not of something else' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->skip()->match('b')
                    ->not()->ref('c')
                    ->getGrammar(),
                false
            ],
            'Sequence with a Skipped Match after an Assert of a Match' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->assert()->match('b')
                    ->skip()->match('c')
                    ->ref('d')
                    ->getGrammar(),
                true
            ],
            'Sequence with a Skipped Match after a Not of a Match' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->not()->match('b')
                    ->skip()->match('c')
                    ->ref('d')
                    ->getGrammar(),
                true
            ],
            'Sequence with a Skipped Match after an Assert of something else' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->assert()->ref('b')
                    ->skip()->match('c')
                    ->getGrammar(),
                false
            ],
            'Sequence with a Skipped Match after a Not of something else' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->not()->ref('b')
                    ->skip()->match('c')
                    ->getGrammar(),
                false
            ],
            'Sequence with non-adjacent predicate & match' => [
                Builder::create()->rule('test')->sequence()
                    ->ref('a')
                    ->skip()->match('b')
                    ->ref('c')
                    ->skip()->match('d')
                    ->getGrammar(),
                false
            ],
        ];
    }
}