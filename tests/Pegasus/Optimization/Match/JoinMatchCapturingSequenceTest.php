<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Optimization\Match;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Terminal\GroupMatch;
use ju1ius\Pegasus\Expression\Terminal\NonCapturingRegExp;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinMatchCapturingSequence;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\GrammarBuilder;

class JoinMatchCapturingSequenceTest extends RegExpOptimizationTestCase
{
    /**
     * @dataProvider provideTestApply
     */
    public function testApply(Grammar $input, Expression $expected)
    {
        $optim = $this->createOptimization(JoinMatchCapturingSequence::class);
        $result = $this->applyOptimization($optim, $input);
        $this->assertExpressionEquals($expected, $result);
    }

    public function provideTestApply(): iterable
    {
        yield 'A sequence of only skipping matches' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ignore()->match('a')
                ->ignore()->literal('b')
                ->ignore()->match('c')
            ->getGrammar(),
            new Ignore(new NonCapturingRegExp('(?>a)(?>b)(?>c)'), 'test')
        ];
        yield 'A sequence of skipping matches before something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ignore()->match('a')
                ->ignore()->literal('b')
                ->ref('c')
                ->getGrammar(),
            new Sequence([
                new Ignore(new NonCapturingRegExp('(?>a)(?>b)')),
                new Reference('c'),
            ], 'test')
        ];
        yield 'A sequence of skipping matches after something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->ignore()->literal('b')
                ->ignore()->match('c')
                ->getGrammar(),
            new Sequence([
                new Reference('a'),
                new Ignore(new NonCapturingRegExp('(?>b)(?>c)')),
            ], 'test')
        ];
        yield 'A sequence of only matches' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->match('a')
                ->literal('b')
                ->match('c')
                ->getGrammar(),
            new GroupMatch(new NonCapturingRegExp('(a)(b)(c)'), 3, 'test')
        ];
        yield 'A sequence of only group matches' => [
            Grammar::fromArray([
                'test' => new Sequence([
                    new GroupMatch(new NonCapturingRegExp('\s*(a)'), 1),
                    new GroupMatch(new NonCapturingRegExp('\s*(b)'), 1),
                    new GroupMatch(new NonCapturingRegExp('\s*(c)'), 1),
                ])
            ]),
            new GroupMatch(new NonCapturingRegExp('(?>\s*(a))(?>\s*(b))(?>\s*(c))'), 3, 'test')
        ];
        yield 'A mix of matches and single-group group matches' => [
            Grammar::fromArray([
                'test' => new Sequence([
                    new GroupMatch(new NonCapturingRegExp('\s*(a)'), 1),
                    new NonCapturingRegExp('[+-]'),
                    new GroupMatch(new NonCapturingRegExp('\s*(b)'), 1),
                ])
            ]),
            new GroupMatch(new NonCapturingRegExp('(?>\s*(a))([+-])(?>\s*(b))'), 3, 'test')
        ];
    }

    /**
     * @dataProvider provideTestAppliesTo
     */
    public function testAppliesTo(Grammar $input, bool $applies)
    {
        $ctx = OptimizationContext::of($input);
        $optim = $this->createOptimization(JoinMatchCapturingSequence::class);
        $result = $optim->willPostProcessExpression($input->getStartExpression(), $ctx);
        $this->assertSame($applies, $result);
    }

    public function provideTestAppliesTo(): iterable
    {
        yield 'Sequence of matches before something non-capturing' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->match('a')
                ->literal('b')
                ->ignore()->ref('c')
                ->getGrammar(),
            true
        ];
        yield 'Sequence of matches before something capturing' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->literal('a')
                ->match('b')
                ->ref('c')
                ->getGrammar(),
            false
        ];
        yield 'Sequence of matches after something non-capturing' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ignore()->ref('a')
                ->match('b')
                ->literal('c')
                ->getGrammar(),
            true
        ];
        yield 'Sequence of matches after something capturing' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->match('c')
                ->getGrammar(),
            false
        ];
        yield 'Sequence of skipping matches before something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ignore()->match('a')
                ->ignore()->match('b')
                ->ref('c')
                ->getGrammar(),
            true
        ];
        yield 'Sequence of skipping matches after something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->ignore()->match('b')
                ->ignore()->match('c')
                ->getGrammar(),
            true
        ];
        yield 'Sequence of single-group group matches before sthg non-capturing' => [
            Grammar::fromArray([
                'test' => new Sequence([
                    new GroupMatch(new NonCapturingRegExp('a'), 1),
                    new GroupMatch(new NonCapturingRegExp('b'), 1),
                    new Ignore(new Reference('c')),
                ], 'test')
            ]),
            true
        ];
        yield 'Sequence of single-group group matches before sthg capturing' => [
            Grammar::fromArray([
                'test' => new Sequence([
                    new GroupMatch(new NonCapturingRegExp('a'), 1),
                    new GroupMatch(new NonCapturingRegExp('b'), 1),
                    new Reference('c'),
                ], 'test')
            ]),
            false
        ];
        yield 'Sequence of single-group group matches after sthg non-capturing' => [
            Grammar::fromArray([
                'test' => new Sequence([
                    new Ignore(new Reference('a')),
                    new GroupMatch(new NonCapturingRegExp('b'), 1),
                    new GroupMatch(new NonCapturingRegExp('c'), 1),
                ], 'test')
            ]),
            true
        ];
        yield 'Sequence of single-group group matches after sthg capturing' => [
            Grammar::fromArray([
                'test' => new Sequence([
                    new Reference('a'),
                    new GroupMatch(new NonCapturingRegExp('b'), 1),
                    new GroupMatch(new NonCapturingRegExp('c'), 1),
                ], 'test')
            ]),
            false
        ];
        yield 'Sequence of multi-group group matches before sthg else' => [
            Grammar::fromArray([
                'test' => new Sequence([
                    new GroupMatch(new NonCapturingRegExp('(a)(b)'), 2),
                    new GroupMatch(new NonCapturingRegExp('(c)(d)'), 2),
                    new Reference('e'),
                ], 'test')
            ]),
            false
        ];
        yield 'Sequence of multi-group group matches after sthg else' => [
            Grammar::fromArray([
                'test' => new Sequence([
                    new Reference('a'),
                    new GroupMatch(new NonCapturingRegExp('(b)(c)'), 2),
                    new GroupMatch(new NonCapturingRegExp('(d)(e)'), 2),
                ], 'test')
            ]),
            false
        ];
        yield 'Sequence with only one match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->ignore()->match('b')
                ->ref('c')
                ->getGrammar(),
            false
        ];
        yield 'Sequence of non-consecutive matches' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ignore()->match('a')
                ->ref('b')
                ->ignore()->match('c')
                ->getGrammar(),
            false
        ];
        yield 'A non-sequence' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->ignore()->match('a')
                ->ignore()->match('b')
                ->ignore()->match('c')
                ->getGrammar(),
            false
        ];
    }
}
