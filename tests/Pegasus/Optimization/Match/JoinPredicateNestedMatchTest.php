<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Optimization\Match;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Terminal\RegExp;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinPredicateNestedMatch;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\GrammarBuilder;

class JoinPredicateNestedMatchTest extends RegExpOptimizationTestCase
{
    /**
     * @dataProvider provideTestApply
     */
    public function testApply(Grammar $input, Expression $expected)
    {
        $optim = $this->createOptimization(JoinPredicateNestedMatch::class);
        $ctx = OptimizationContext::of($input);

        $result = $this->applyOptimization($optim, $input, $ctx);
        $this->assertExpressionEquals($expected, $result, 'In capturing context');

        $result = $this->applyOptimization($optim, $input, $ctx->matching());
        $this->assertExpressionEquals($expected, $result, 'In matching context');
    }

    public function provideTestApply(): iterable
    {
        yield 'Sequence with a Skipped Match before an Assert of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->ignore()->match('b')
                ->assert()->match('c')
                ->ref('d')
                ->getGrammar(),
            new Sequence([
                new Reference('a'),
                new Ignore(new RegExp('(?>b)(?=c)')),
                new Reference('d'),
            ], 'test')
        ];
        yield 'Sequence with a Skipped Match before a Not of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->ignore()->match('b')
                ->not()->match('c')
                ->ref('d')
                ->getGrammar(),
            new Sequence([
                new Reference('a'),
                new Ignore(new RegExp('(?>b)(?!c)')),
                new Reference('d'),
            ], 'test')
        ];
        yield 'Sequence with a Skipped Match before EOF' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->ignore()->match('b')
                ->eof()
                ->ref('d')
                ->getGrammar(),
            new Sequence([
                new Reference('a'),
                new Ignore(new RegExp('(?>b)\z')),
                new Reference('d'),
            ], 'test')
        ];
        yield 'Sequence with a Skipped Match after an Assert of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->assert()->match('b')
                ->ignore()->match('c')
                ->ref('d')
                ->getGrammar(),
            new Sequence([
                new Reference('a'),
                new Ignore(new RegExp('(?=b)(?>c)')),
                new Reference('d'),
            ], 'test')
        ];
        yield 'Sequence with a Skipped Match after a Not of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->not()->match('b')
                ->ignore()->match('c')
                ->ref('d')
                ->getGrammar(),
            new Sequence([
                new Reference('a'),
                new Ignore(new RegExp('(?!b)(?>c)')),
                new Reference('d'),
            ], 'test')
        ];
    }

    /**
     * @dataProvider provideTestAppliesTo
     */
    public function testAppliesTo(Grammar $input, bool $applies)
    {
        $optim = $this->createOptimization(JoinPredicateNestedMatch::class);
        $expr = $input->getStartExpression();
        $ctx = OptimizationContext::of($input);

        $result = $optim->willPostProcessExpression($expr, $ctx);
        $this->assertSame($applies, $result, 'In capturing context');

        $result = $optim->willPostProcessExpression($expr, $ctx->matching());
        $this->assertSame($applies, $result, 'In matching context');
    }

    public function provideTestAppliesTo(): iterable
    {
        yield 'Sequence with a Skipped Match before an Assert of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->ignore()->match('b')
                ->assert()->match('c')
                ->ref('d')
                ->getGrammar(),
            true
        ];
        yield 'Sequence with a Skipped Match before a Not of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->ignore()->match('b')
                ->not()->match('c')
                ->ref('d')
                ->getGrammar(),
            true
        ];
        yield 'Sequence with a Skipped Match before EOF' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->ignore()->match('b')
                ->eof()
                ->ref('d')
                ->getGrammar(),
            true
        ];
        yield 'Sequence with a Skipped Match before an Assert of something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->ignore()->match('b')
                ->assert()->ref('c')
                ->getGrammar(),
            false
        ];
        yield 'Sequence with a Skipped Match before a Not of something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->ignore()->match('b')
                ->not()->ref('c')
                ->getGrammar(),
            false
        ];
        yield 'Sequence with a Skipped Match after an Assert of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->assert()->match('b')
                ->ignore()->match('c')
                ->ref('d')
                ->getGrammar(),
            true
        ];
        yield 'Sequence with a Skipped Match after a Not of a Match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->not()->match('b')
                ->ignore()->match('c')
                ->ref('d')
                ->getGrammar(),
            true
        ];
        yield 'Sequence with a Skipped Match after an Assert of something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->assert()->ref('b')
                ->ignore()->match('c')
                ->getGrammar(),
            false
        ];
        yield 'Sequence with a Skipped Match after a Not of something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->not()->ref('b')
                ->ignore()->match('c')
                ->getGrammar(),
            false
        ];
        yield 'Sequence with non-adjacent predicate & match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->ignore()->match('b')
                ->ref('c')
                ->ignore()->match('d')
                ->getGrammar(),
            false
        ];
    }
}
