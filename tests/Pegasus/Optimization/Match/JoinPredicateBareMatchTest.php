<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Optimization\Match;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Terminal\RegExp;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinPredicateBareMatch;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\PegasusAssert;
use PHPUnit\Framework\Assert;

class JoinPredicateBareMatchTest extends RegExpOptimizationTestCase
{
    /**
     * @dataProvider provideTestApply
     */
    public function testApply(Grammar $input, Expression $expected)
    {
        $optim = $this->createOptimization(JoinPredicateBareMatch::class);
        $ctx = OptimizationContext::of($input);

        $result = $this->applyOptimization($optim, $input, $ctx);
        PegasusAssert::ExpressionEquals($expected, $result, 'In capturing context');

        $result = $this->applyOptimization($optim, $input, $ctx->matching());
        PegasusAssert::ExpressionEquals($expected, $result, 'In matching context');
    }

    public function provideTestApply(): iterable
    {
        yield 'Sequence with a Match before an Assert of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->assert()->match('c')
                ->ref('d')
                ->getGrammar(),
            new Sequence([
                new Reference('a'),
                new RegExp('(?>b)(?=c)'),
                new Reference('d'),
            ], 'test')
        ];
        yield 'Sequence with a Match before a Not of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->not()->match('c')
                ->ref('d')
                ->getGrammar(),
            new Sequence([
                new Reference('a'),
                new RegExp('(?>b)(?!c)'),
                new Reference('d'),
            ], 'test')
        ];
        yield 'Sequence with a Match before EOF' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->eof()
                ->ref('c')
                ->getGrammar(),
            new Sequence([
                new Reference('a'),
                new RegExp('(?>b)\z'),
                new Reference('c'),
            ], 'test')
        ];
        yield 'Sequence with a Match after an Assert of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->assert()->match('b')
                ->match('c')
                ->ref('d')
                ->getGrammar(),
            new Sequence([
                new Reference('a'),
                new RegExp('(?=b)(?>c)'),
                new Reference('d'),
            ], 'test')
        ];
        yield 'Sequence with a Match after a Not of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->not()->match('b')
                ->match('c')
                ->ref('d')
                ->getGrammar(),
            new Sequence([
                new Reference('a'),
                new RegExp('(?!b)(?>c)'),
                new Reference('d'),
            ], 'test')
        ];
    }

    /**
     * @dataProvider provideTestAppliesTo
     */
    public function testAppliesTo(Grammar $input, bool $applies)
    {
        $optim = $this->createOptimization(JoinPredicateBareMatch::class);
        $expr = $input->getStartExpression();
        $ctx = OptimizationContext::of($input);

        $result = $optim->willPostProcessExpression($expr, $ctx);
        Assert::assertSame($applies, $result, 'In capturing context');

        $result = $optim->willPostProcessExpression($expr, $ctx->matching());
        Assert::assertSame($applies, $result, 'In matching context');
    }

    public function provideTestAppliesTo(): iterable
    {
        yield 'Sequence with a Match before an Assert of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->assert()->match('c')
                ->ref('d')
                ->getGrammar(),
            true
        ];
        yield 'Sequence with a Match before a Not of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->not()->match('c')
                ->ref('d')
                ->getGrammar(),
            true
        ];
        yield 'Sequence with a Match before EOF' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->eof()
                ->ref('c')
                ->getGrammar(),
            true
        ];
        yield 'Sequence with a Match before an Assert of something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->assert()->ref('c')
                ->ref('d')
                ->getGrammar(),
            false
        ];
        yield 'Sequence with a Match before a Not of something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->not()->ref('c')
                ->ref('d')
                ->getGrammar(),
            false
        ];
        yield 'Sequence with a Match after an Assert of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->assert()->match('b')
                ->match('c')
                ->ref('d')
                ->getGrammar(),
            true
        ];
        yield 'Sequence with a Match after a Not of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->not()->match('b')
                ->match('c')
                ->ref('d')
                ->getGrammar(),
            true
        ];
        yield 'Sequence with a Match after an Assert of something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->assert()->ref('b')
                ->match('c')
                ->ref('d')
                ->getGrammar(),
            false
        ];
        yield 'Sequence with a Match after a Not of something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->not()->ref('b')
                ->match('c')
                ->ref('d')
                ->getGrammar(),
            false
        ];
        yield 'Sequence with a Match after a EOF' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->eof()
                ->match('c')
                ->ref('d')
                ->getGrammar(),
            true
        ];
        yield 'Sequence with non-consecutive match & predicate' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->ref('c')
                ->assert()->match('d')
                ->getGrammar(),
            false
        ];
    }
}
