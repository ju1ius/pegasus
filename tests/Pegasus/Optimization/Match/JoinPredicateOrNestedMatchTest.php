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
use ju1ius\Pegasus\Expression\Skip;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinPredicateOrNestedMatch;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\Tests\Optimization\OptimizationTestCase;

class JoinPredicateOrNestedMatchTest extends OptimizationTestCase
{
    /**
     * @dataProvider getApplyProvider
     *
     * @param Grammar    $input
     * @param Expression $expected
     */
    public function testApply(Grammar $input, Expression $expected)
    {
        $optim = new JoinPredicateOrNestedMatch();
        $ctx = OptimizationContext::of($input);

        $result = $this->applyOptimization($optim, $input, $ctx);
        $this->assertExpressionEquals($expected, $result, 'In capturing context');

        $result = $this->applyOptimization($optim, $input, $ctx->matching());
        $this->assertExpressionEquals($expected, $result, 'In matching context');
    }

    public function getApplyProvider()
    {
        return [
            'Choice with Skipped Match before an Assert of a Match' => [
                Grammar\Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->skip()->match('b')
                    ->assert()->match('c')
                    ->getGrammar(),
                new OneOf([
                    new Reference('a'),
                    new Skip(new Match('b|(?=c)'))
                ], 'test')
            ],
            'Choice with Skipped Match before a Not of a Match' => [
                Grammar\Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->skip()->match('b')
                    ->not()->match('c')
                    ->getGrammar(),
                new OneOf([
                    new Reference('a'),
                    new Skip(new Match('b|(?!c)'))
                ], 'test')
            ],
            'Choice with Skipped Match before a EOF' => [
                Grammar\Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->skip()->match('b')
                    ->eof()
                    ->getGrammar(),
                new OneOf([
                    new Reference('a'),
                    new Skip(new Match('b|\z'))
                ], 'test')
            ],
            'Choice with Skipped Match after an Assert of a Match' => [
                Grammar\Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->assert()->match('b')
                    ->skip()->match('c')
                    ->getGrammar(),
                new OneOf([
                    new Reference('a'),
                    new Skip(new Match('(?=b)|c'))
                ], 'test')
            ],
            'Choice with Skipped Match after a Not of a Match' => [
                Grammar\Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->not()->match('b')
                    ->skip()->match('c')
                    ->getGrammar(),
                new OneOf([
                    new Reference('a'),
                    new Skip(new Match('(?!b)|c'))
                ], 'test')
            ],
            'Choice with Skipped Match after EOF' => [
                Grammar\Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->eof()
                    ->skip()->match('c')
                    ->getGrammar(),
                new OneOf([
                    new Reference('a'),
                    new Skip(new Match('\z|c'))
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
        $optim = new JoinPredicateOrNestedMatch();
        $expr = $input->getStartExpression();
        $ctx = OptimizationContext::of($input);

        $result = $optim->willPostProcessExpression($expr, $ctx);
        $this->assertSame($applies, $result, 'In capturing context');

        $result = $optim->willPostProcessExpression($expr, $ctx->matching());
        $this->assertSame($applies, $result, 'In matching context');
    }

    public function getAppliesToProvider()
    {
        return [
            'Choice with Skipped Match before an Assert of a Match' => [
                Grammar\Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->skip()->match('b')
                    ->assert()->match('c')
                    ->getGrammar(),
                true
            ],
            'Choice with Skipped Match before a Not of a Match' => [
                Grammar\Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->skip()->match('b')
                    ->not()->match('c')
                    ->getGrammar(),
                true
            ],
            'Choice with Skipped Match before a EOF' => [
                Grammar\Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->skip()->match('b')
                    ->eof()
                    ->getGrammar(),
                true
            ],
            'Choice with Skipped Match after an Assert of a Match' => [
                Grammar\Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->assert()->match('b')
                    ->skip()->match('c')
                    ->getGrammar(),
                true
            ],
            'Choice with Skipped Match after a Not of a Match' => [
                Grammar\Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->not()->match('b')
                    ->skip()->match('c')
                    ->getGrammar(),
                true
            ],
            'Choice with Skipped Match after EOF' => [
                Grammar\Builder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->eof()
                    ->skip()->match('c')
                    ->getGrammar(),
                true
            ],
        ];
    }
}
