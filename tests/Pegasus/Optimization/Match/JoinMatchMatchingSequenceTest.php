<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Optimization\Match;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Terminal\RegExp;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization\MatchJoining\JoinMatchMatchingSequence;
use ju1ius\Pegasus\Grammar\OptimizationContext;
use ju1ius\Pegasus\GrammarBuilder;

class JoinMatchMatchingSequenceTest extends RegExpOptimizationTestCase
{
    /**
     * @dataProvider provideTestApply
     */
    public function testApply(Grammar $input, Expression $expected)
    {
        $ctx = OptimizationContext::of($input, OptimizationContext::TYPE_MATCHING);
        $optim = $this->createOptimization(JoinMatchMatchingSequence::class);
        $result = $this->applyOptimization($optim, $input, $ctx);
        $this->assertExpressionEquals($expected, $result);
    }

    public function provideTestApply(): iterable
    {
        yield 'Joins a sequence of only matches' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->match('a')
                ->match('b')
                ->match('c')
                ->getGrammar(),
            new RegExp('(?>a)(?>b)(?>c)', [], 'test'),
        ];
        yield 'Joins a sequence of only matches with flags' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->match('a', ['i'])
                ->match('b', ['m'])
                ->match('c')
                ->getGrammar(),
            new RegExp('(?>(?i:a))(?>(?m:b))(?>c)', [], 'test'),
        ];
        yield 'Joins a sequence of literals' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->literal('a/b')
                ->literal('c?')
                ->literal('{d}')
                ->getGrammar(),
            new RegExp('(?>a\/b)(?>c\?)(?>\{d\})', [], 'test'),
        ];
        yield 'Joins a sequence of literals or matches' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->literal('foo/bar')
                ->match('c+|d')
                ->literal('baz')
                ->getGrammar(),
            new RegExp('(?>foo\/bar)(?>c+|d)(?>baz)', [], 'test'),
        ];
        yield 'Joins consecutive matches before something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->match('a', ['i'])
                ->match('b')
                ->ref('c')
                ->getGrammar(),
            new Sequence([
                new RegExp('(?>(?i:a))(?>b)'),
                new Reference('c'),
            ], 'test'),
        ];
        yield 'Joins consecutive matches after something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('c')
                ->match('a', ['i'])
                ->match('b')
                ->getGrammar(),
            new Sequence([
                new Reference('c'),
                new RegExp('(?>(?i:a))(?>b)'),
            ], 'test'),
        ];
    }

    /**
     * @dataProvider provideTestAppliesTo
     */
    public function testAppliesTo(Grammar $input, int $contextType, bool $applies)
    {
        $ctx = OptimizationContext::of($input, $contextType);
        $optim = $this->createOptimization(JoinMatchMatchingSequence::class);
        $result = $optim->willPostProcessExpression($input->getStartExpression(), $ctx);
        $this->assertSame($applies, $result);
    }

    public function provideTestAppliesTo(): iterable
    {
        yield 'A sequence of matches in a matching context' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->match('a')
                ->literal('b')
                ->match('c')
                ->getGrammar(),
            OptimizationContext::TYPE_MATCHING,
            true,
        ];
        yield 'A sequence of matches in a capturing context' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->match('a')
                ->literal('b')
                ->match('c')
                ->getGrammar(),
            OptimizationContext::TYPE_CAPTURING,
            false,
        ];
        yield 'Consecutive matches before something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->match('a', ['i'])
                ->match('b')
                ->ref('c')
                ->getGrammar(),
            OptimizationContext::TYPE_MATCHING,
            true,
        ];
        yield 'Consecutive matches after something else' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('c')
                ->match('a', ['i'])
                ->match('b')
                ->getGrammar(),
            OptimizationContext::TYPE_MATCHING,
            true,
        ];
        yield 'A sequence with only one match' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->ref('a')
                ->match('b')
                ->ref('c')
                ->getGrammar(),
            OptimizationContext::TYPE_MATCHING,
            false,
        ];
        yield 'Non-consecutive matches' => [
            GrammarBuilder::create()->rule('test')->sequence()
                ->match('a')
                ->ref('b')
                ->match('c')
                ->getGrammar(),
            OptimizationContext::TYPE_MATCHING,
            false,
        ];
        yield 'A non sequence' => [
            GrammarBuilder::create()->rule('test')->oneOf()
                ->match('a')
                ->match('b')
                ->match('c')
                ->getGrammar(),
            OptimizationContext::TYPE_MATCHING,
            false,
        ];
    }
}
