<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Optimization\Match;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Terminal\RegExp;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinPredicateOrNestedMatch;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\PegasusAssert;
use PHPUnit\Framework\Assert;

class JoinPredicateOrNestedMatchTest extends RegExpOptimizationTestCase
{
    /**
     * @dataProvider provideTestApply
     */
    public function testApply(Grammar $input, Expression $expected)
    {
        $optim = $this->createOptimization(JoinPredicateOrNestedMatch::class);
        $ctx = OptimizationContext::of($input);

        $result = $this->applyOptimization($optim, $input, $ctx);
        PegasusAssert::ExpressionEquals($expected, $result, 'In capturing context');

        $result = $this->applyOptimization($optim, $input, $ctx->matching());
        PegasusAssert::ExpressionEquals($expected, $result, 'In matching context');
    }

    public function provideTestApply(): iterable
    {
        yield 'Choice with Skipped Match before an Assert of a Match' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->ref('a')
                ->ignore()->match('b')
                ->assert()->match('c')
                ->getGrammar(),
            new OneOf([
                new Reference('a'),
                new Ignore(new RegExp('b|(?=c)'))
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
                new Ignore(new RegExp('b|(?!c)'))
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
                new Ignore(new RegExp('b|\z'))
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
                new Ignore(new RegExp('(?=b)|c'))
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
                new Ignore(new RegExp('(?!b)|c'))
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
                new Ignore(new RegExp('\z|c'))
            ], 'test')
        ];
    }

    /**
     * @dataProvider provideTestAppliesTo
     */
    public function testAppliesTo(Grammar $input, bool $applies)
    {
        $optim = $this->createOptimization(JoinPredicateOrNestedMatch::class);
        $expr = $input->getStartExpression();
        $ctx = OptimizationContext::of($input);

        $result = $optim->willPostProcessExpression($expr, $ctx);
        Assert::assertSame($applies, $result, 'In capturing context');

        $result = $optim->willPostProcessExpression($expr, $ctx->matching());
        Assert::assertSame($applies, $result, 'In matching context');
    }

    public function provideTestAppliesTo(): iterable
    {
        yield 'Choice with Skipped Match before an Assert of a Match' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->ref('a')
                ->ignore()->match('b')
                ->assert()->match('c')
                ->getGrammar(),
            true
        ];
        yield 'Choice with Skipped Match before a Not of a Match' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->ref('a')
                ->ignore()->match('b')
                ->not()->match('c')
                ->getGrammar(),
            true
        ];
        yield 'Choice with Skipped Match before a EOF' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->ref('a')
                ->ignore()->match('b')
                ->eof()
                ->getGrammar(),
            true
        ];
        yield 'Choice with Skipped Match after an Assert of a Match' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->ref('a')
                ->assert()->match('b')
                ->ignore()->match('c')
                ->getGrammar(),
            true
        ];
        yield 'Choice with Skipped Match after a Not of a Match' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->ref('a')
                ->not()->match('b')
                ->ignore()->match('c')
                ->getGrammar(),
            true
        ];
        yield 'Choice with Skipped Match after EOF' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->ref('a')
                ->eof()
                ->ignore()->match('c')
                ->getGrammar(),
            true
        ];
    }
}
