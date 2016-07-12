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
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Optimization\Match\JoinPredicateOrBareMatch;
use ju1ius\Pegasus\Optimization\OptimizationContext;
use ju1ius\Pegasus\Tests\Optimization\OptimizationTestCase;

class JoinPredicateOrBareMatchTest extends OptimizationTestCase
{
    /**
     * @dataProvider getApplyProvider
     *
     * @param Grammar    $input
     * @param Expression $expected
     */
    public function testApply(Grammar $input, Expression $expected)
    {
        $optim = new JoinPredicateOrBareMatch();
        $ctx = OptimizationContext::create($input);

        $result = $this->applyOptimization($optim, $input, $ctx);
        $this->assertExpressionEquals($expected, $result, 'In capturing context');

        $result = $this->applyOptimization($optim, $input, $ctx->matching());
        $this->assertExpressionEquals($expected, $result, 'In matching context');
    }

    public function getApplyProvider()
    {
        return [
            'Choice with Match before an Assert of a Match' => [
                Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->match('b')
                    ->assert()->match('c')
                    ->getGrammar(),
                new OneOf([
                    new Reference('a'),
                    new Match('b|(?=c)')
                ], 'test')
            ],
            'Choice with Match before a Not of a Match' => [
                Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->match('b')
                    ->not()->match('c')
                    ->getGrammar(),
                new OneOf([
                    new Reference('a'),
                    new Match('b|(?!c)')
                ], 'test')
            ],
            'Choice with Match before EOF' => [
                Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->match('b')
                    ->eof()
                    ->getGrammar(),
                new OneOf([
                    new Reference('a'),
                    new Match('b|\z')
                ], 'test')
            ],
            'Choice with Match after an Assert of a Match' => [
                Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->assert()->match('b')
                    ->match('c')
                    ->getGrammar(),
                new OneOf([
                    new Reference('a'),
                    new Match('(?=b)|c')
                ], 'test')
            ],
            'Choice with Match after a Not of a Match' => [
                Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->not()->match('b')
                    ->match('c')
                    ->getGrammar(),
                new OneOf([
                    new Reference('a'),
                    new Match('(?!b)|c')
                ], 'test')
            ],
            'Choice with Match after EOF' => [
                Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->eof()
                    ->match('c')
                    ->getGrammar(),
                new OneOf([
                    new Reference('a'),
                    new Match('\z|c')
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
        $optim = new JoinPredicateOrBareMatch();
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
            'Choice with Match before an Assert of a Match' => [
                Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->match('b')
                    ->assert()->match('c')
                    ->getGrammar(),
                true
            ],
            'Choice with Match before a Not of a Match' => [
                Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->match('b')
                    ->not()->match('c')
                    ->getGrammar(),
                true
            ],
            'Choice with Match before EOF' => [
                Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->match('b')
                    ->eof()
                    ->getGrammar(),
                true
            ],
            'Choice with Match after an Assert of a Match' => [
                Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->assert()->match('b')
                    ->match('c')
                    ->getGrammar(),
                true
            ],
            'Choice with Match after a Not of a Match' => [
                Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->not()->match('b')
                    ->match('c')
                    ->getGrammar(),
                true
            ],
            'Choice with Match after EOF' => [
                Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->eof()
                    ->match('c')
                    ->getGrammar(),
                true
            ],
        ];
    }
}