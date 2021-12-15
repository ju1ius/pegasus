<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Optimization\Match;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Terminal\NonCapturingRegExp;
use ju1ius\Pegasus\ExpressionBuilder;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization\CombineQuantifiedMatch;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\GrammarBuilder as GrammarBuilder;

class CombineQuantifiedMatchTest extends RegExpOptimizationTestCase
{
    /**
     * @dataProvider provideTestAcceptsExpression
     */
    public function testAcceptsExpression(Expression $expr, int $contextType, bool $expected)
    {
        $grammar = $this->createMock(Grammar::class);
        $ctx = OptimizationContext::of($grammar, $contextType);
        $optim = $this->createOptimization(CombineQuantifiedMatch::class);
        $result = $optim->willPostProcessExpression($expr, $ctx);
        $this->assertSame($expected, $result);
    }

    public function provideTestAcceptsExpression(): iterable
    {
        yield 'Returns false in capturing context.' => [
            ExpressionBuilder::create()->between(2, 4)->match('a')->getExpression(),
            OptimizationContext::TYPE_CAPTURING,
            false,
        ];
        yield 'Applies to a quantified match in matching context.' => [
            ExpressionBuilder::create()->between(2, 4)->match('a')->getExpression(),
            OptimizationContext::TYPE_MATCHING,
            true,
        ];
        yield 'Applies to a quantified regexp in matching context.' => [
            ExpressionBuilder::create()->between(2, 4)->regexp('a')->getExpression(),
            OptimizationContext::TYPE_MATCHING,
            true,
        ];
        yield 'Applies to a quantified literal in matching context.' => [
            ExpressionBuilder::create()->between(2, 4)->literal('a')->getExpression(),
            OptimizationContext::TYPE_MATCHING,
            true,
        ];
        yield 'Returns false for quantified expression other than match, regexp or literal.' => [
            ExpressionBuilder::create()->between(2, 4)->epsilon()->getExpression(),
            OptimizationContext::TYPE_MATCHING,
            false,
        ];
        yield 'Returns false for anything other than quantifier.' => [
            ExpressionBuilder::create()->assert()->literal('a')->getExpression(),
            OptimizationContext::TYPE_MATCHING,
            false,
        ];
    }

    /**
     * @dataProvider provideTestApply
     */
    public function testApply(Grammar $grammar, Expression $expected)
    {
        $ctx = OptimizationContext::of($grammar, OptimizationContext::TYPE_MATCHING);
        $optim = $this->createOptimization(CombineQuantifiedMatch::class);
        $result = $this->applyOptimization($optim, $grammar, $ctx);
        $this->assertExpressionEquals($expected, $result);
    }

    public function provideTestApply(): iterable
    {
        yield 'Quantified match with an upper bound' => [
            GrammarBuilder::create()->rule('test')
                ->between(2, 4)->match('a')
                ->getGrammar(),
            new NonCapturingRegExp('(?>a){2,4}', [], 'test')
        ];
        yield 'Quantified match with no upper bound' => [
            GrammarBuilder::create()->rule('test')
                ->atLeast(2)->match('a')
                ->getGrammar(),
            new NonCapturingRegExp('(?>a){2,}', [], 'test')
        ];
        yield 'Exact quantified match' => [
            GrammarBuilder::create()->rule('test')
                ->exactly(2)->match('a')
                ->getGrammar(),
            new NonCapturingRegExp('(?>a){2}', [], 'test')
        ];
        yield 'zero-or-more quantified match' => [
            GrammarBuilder::create()->rule('test')
                ->zeroOrMore()->match('a')
                ->getGrammar(),
            new NonCapturingRegExp('(?>a)*', [], 'test')
        ];
        yield 'one-or-more quantified match' => [
            GrammarBuilder::create()->rule('test')
                ->oneOrMore()->match('a')
                ->getGrammar(),
            new NonCapturingRegExp('(?>a)+', [], 'test')
        ];
        yield 'optional match' => [
            GrammarBuilder::create()->rule('test')
                ->optional()->match('a')
                ->getGrammar(),
            new NonCapturingRegExp('(?>a)?', [], 'test')
        ];
    }
}
