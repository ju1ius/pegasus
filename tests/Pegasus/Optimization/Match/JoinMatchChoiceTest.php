<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Optimization\Match;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Terminal\RegExp;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinMatchChoice;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\PegasusAssert;
use PHPUnit\Framework\Assert;

class JoinMatchChoiceTest extends RegExpOptimizationTestCase
{
    /**
     * @dataProvider provideTestApply
     */
    public function testApply(Grammar $input, Expression $expected)
    {
        $optim = $this->createOptimization(JoinMatchChoice::class);
        $ctx = OptimizationContext::of($input);

        $result = $this->applyOptimization($optim, $input, $ctx);
        PegasusAssert::ExpressionEquals($expected, $result, 'In capturing context');

        $result = $this->applyOptimization($optim, $input, $ctx->matching());
        PegasusAssert::ExpressionEquals($expected, $result, 'In matching context');
    }

    public function provideTestApply(): iterable
    {
        yield 'Choice of matches only' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->match('a')
                ->match('b')
                ->match('c')
                ->getGrammar(),
            new RegExp('a|b|c', [], 'test'),
        ];
        yield 'Choice of matches before something else' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->match('a')
                ->match('b')
                ->ref('c')
                ->getGrammar(),
            new OneOf([
                new RegExp('a|b'),
                new Reference('c'),
            ], 'test'),
        ];
        yield 'Choice of matches after something else' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->ref('a')
                ->match('b')
                ->match('c')
                ->getGrammar(),
            new OneOf([
                new Reference('a'),
                new RegExp('b|c'),
            ], 'test'),
        ];
    }

    /**
     * @dataProvider provideTestAppliesTo
     */
    public function testAppliesTo(Grammar $input, bool $applies)
    {
        $optim = $this->createOptimization(JoinMatchChoice::class);
        $expr = $input->getStartExpression();
        $ctx = OptimizationContext::of($input);

        $result = $optim->willPostProcessExpression($expr, $ctx);
        Assert::assertSame($applies, $result, 'In capturing context');

        $result = $optim->willPostProcessExpression($expr, $ctx->matching());
        Assert::assertSame($applies, $result, 'In matching context');
    }

    public function provideTestAppliesTo(): iterable
    {
        return [
            'Choice of matches only' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->match('a')
                    ->match('b')
                    ->match('c')
                    ->getGrammar(),
                true,
            ],
            'Choice of matches before something else' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->match('a')
                    ->match('b')
                    ->ref('c')
                    ->getGrammar(),
                true,
            ],
            'Choice of matches after something else' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->match('b')
                    ->match('c')
                    ->getGrammar(),
                true,
            ],
            'Choice with only one match' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->ref('a')
                    ->match('b')
                    ->ref('c')
                    ->getGrammar(),
                false,
            ],
            'Choice with non-consecutive matches' => [
                GrammarBuilder::create()->rule('test')->oneOf()
                    ->match('a')
                    ->ref('b')
                    ->match('c')
                    ->getGrammar(),
                false,
            ],
            'Not a choice' => [
                GrammarBuilder::create()->rule('test')->sequence()
                    ->match('a')
                    ->match('b')
                    ->match('c')
                    ->getGrammar(),
                false,
            ],
        ];
    }
}
