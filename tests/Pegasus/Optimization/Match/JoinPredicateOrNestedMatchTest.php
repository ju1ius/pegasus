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
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinPredicateOrNestedMatch;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\GrammarBuilder;

class JoinPredicateOrNestedMatchTest extends RegExpOptimizationTestCase
{
    /**
     * @dataProvider provideTestApply
     *
     * @param Grammar    $input
     * @param Expression $expected
     */
    public function testApply(Grammar $input, Expression $expected)
    {
        $optim = $this->createOptimization(JoinPredicateOrNestedMatch::class);
        $ctx = OptimizationContext::of($input);

        $result = $this->applyOptimization($optim, $input, $ctx);
        $this->assertExpressionEquals($expected, $result, 'In capturing context');

        $result = $this->applyOptimization($optim, $input, $ctx->matching());
        $this->assertExpressionEquals($expected, $result, 'In matching context');
    }

    public function provideTestApply()
    {
        yield 'Choice with Skipped Match before an Assert of a Match' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->ref('a')
                ->ignore()->match('b')
                ->assert()->match('c')
                ->getGrammar(),
            new OneOf([
                new Reference('a'),
                new Ignore(new Match('b|(?=c)'))
            ], 'test')
        ];
        yield 'Choice with Skipped Match before a Not of a Match' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->ref('a')
                ->ignore()->match('b')
                ->not()->match('c')
                ->getGrammar(),
            new OneOf([
                new Reference('a'),
                new Ignore(new Match('b|(?!c)'))
            ], 'test')
        ];
        yield 'Choice with Skipped Match before a EOF' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->ref('a')
                ->ignore()->match('b')
                ->eof()
                ->getGrammar(),
            new OneOf([
                new Reference('a'),
                new Ignore(new Match('b|\z'))
            ], 'test')
        ];
        yield 'Choice with Skipped Match after an Assert of a Match' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->ref('a')
                ->assert()->match('b')
                ->ignore()->match('c')
                ->getGrammar(),
            new OneOf([
                new Reference('a'),
                new Ignore(new Match('(?=b)|c'))
            ], 'test')
        ];
        yield 'Choice with Skipped Match after a Not of a Match' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->ref('a')
                ->not()->match('b')
                ->ignore()->match('c')
                ->getGrammar(),
            new OneOf([
                new Reference('a'),
                new Ignore(new Match('(?!b)|c'))
            ], 'test')
        ];
        yield 'Choice with Skipped Match after EOF' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->ref('a')
                ->eof()
                ->ignore()->match('c')
                ->getGrammar(),
            new OneOf([
                new Reference('a'),
                new Ignore(new Match('\z|c'))
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
        $optim = $this->createOptimization(JoinPredicateOrNestedMatch::class);
        $expr = $input->getStartExpression();
        $ctx = OptimizationContext::of($input);

        $result = $optim->willPostProcessExpression($expr, $ctx);
        $this->assertSame($applies, $result, 'In capturing context');

        $result = $optim->willPostProcessExpression($expr, $ctx->matching());
        $this->assertSame($applies, $result, 'In matching context');
    }

    public function provideTestAppliesTo()
    {
        return [
            'Choice with Skipped Match before an Assert of a Match' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->ignore()->match('b')
                    ->assert()->match('c')
                    ->getGrammar(),
                true
            ],
            'Choice with Skipped Match before a Not of a Match' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->ignore()->match('b')
                    ->not()->match('c')
                    ->getGrammar(),
                true
            ],
            'Choice with Skipped Match before a EOF' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->ignore()->match('b')
                    ->eof()
                    ->getGrammar(),
                true
            ],
            'Choice with Skipped Match after an Assert of a Match' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->assert()->match('b')
                    ->ignore()->match('c')
                    ->getGrammar(),
                true
            ],
            'Choice with Skipped Match after a Not of a Match' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->not()->match('b')
                    ->ignore()->match('c')
                    ->getGrammar(),
                true
            ],
            'Choice with Skipped Match after EOF' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->eof()
                    ->ignore()->match('c')
                    ->getGrammar(),
                true
            ],
        ];
    }
}
