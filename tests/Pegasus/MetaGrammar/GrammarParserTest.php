<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\MetaGrammar;

use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Expression\Application\Super;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Decorator\Assert;
use ju1ius\Pegasus\Expression\Decorator\Cut;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Decorator\Label;
use ju1ius\Pegasus\Expression\Decorator\NodeAction;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\Decorator\OneOrMore;
use ju1ius\Pegasus\Expression\Decorator\Optional;
use ju1ius\Pegasus\Expression\Decorator\Quantifier;
use ju1ius\Pegasus\Expression\Decorator\Token;
use ju1ius\Pegasus\Expression\Decorator\ZeroOrMore;
use ju1ius\Pegasus\Expression\Terminal\BackReference;
use ju1ius\Pegasus\Expression\Terminal\CapturingRegExp;
use ju1ius\Pegasus\Expression\Terminal\EOF;
use ju1ius\Pegasus\Expression\Terminal\Epsilon;
use ju1ius\Pegasus\Expression\Terminal\Fail;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\Word;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimizer;
use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\MetaGrammar\MetaGrammarTransform;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Parser\LeftRecursivePackrat;
use ju1ius\Pegasus\Tests\PegasusTestCase;

class GrammarParserTest extends PegasusTestCase
{
    private function parseSyntax($syntax, $optimizationLevel = 0, $optimizedMeta = false)
    {
        $meta = $optimizedMeta ? MetaGrammar::create() : MetaGrammar::getGrammar();
        $parser = new LeftRecursivePackrat($meta);
        try {
            $tree = $parser->parse($syntax);
        } catch (ParseError $err) {
            $this->fail($err->getMessage());
        }
        $grammar = (new MetaGrammarTransform())->transform($tree);
        if ($optimizationLevel) {
            return Optimizer::optimize($grammar, $optimizationLevel);
        }

        return $grammar;
    }

    /**
     * @dataProvider provideTestItParsesTerminalRules
     */
    public function testItParsesTerminalRules(string $syntax, Grammar $expected)
    {
        $grammar = $this->parseSyntax($syntax);
        $this->assertGrammarEquals($expected, $grammar);
    }

    /**
     * @dataProvider provideTestItParsesTerminalRules
     */
    public function testItParsesTerminalRulesWithOptimizedMeta(string $syntax, Grammar $expected)
    {
        $grammar = $this->parseSyntax($syntax, 0, true);
        $this->assertGrammarEquals($expected, $grammar);
    }

    public function provideTestItParsesTerminalRules(): iterable
    {
        yield 'double-quoted literal' => [
            'x = "x"',
            Grammar::fromArray(['x' => new Literal('x', 'x', '"')])
        ];
        yield 'double-quoted literal with escaped characters' => [
            'x = "x \" x"',
            Grammar::fromArray(['x' => new Literal('x " x', 'x', '"')])
        ];
        yield 'single-quoted literal' => [
            "x = 'x'",
            Grammar::fromArray(['x' => new Literal('x', 'x', "'")])
        ];
        yield 'single-quoted literal with escaped characters' => [
            "x = 'x \' x'",
            Grammar::fromArray(['x' => new Literal("x \' x", 'x', "'")])
        ];
        yield 'match' => [
            'x = /x/',
            Grammar::fromArray(['x' => new CapturingRegExp('x', [], 'x')])
        ];
        yield 'match with escaped delimiter' => [
            'x = /x \/ x/',
            Grammar::fromArray(['x' => new CapturingRegExp('x \/ x', [], 'x')])
        ];
        yield 'match with flags' => [
            'x = /x/i',
            Grammar::fromArray(['x' => new CapturingRegExp('x', ['i'], 'x')])
        ];
        yield 'match non-capturing groups' => [
            'x = /(?:x)(?!y)/',
            Grammar::fromArray(['x' => new CapturingRegExp('(?:x)(?!y)', [], 'x')])
        ];
        yield 'RegExp' => [
            'x = /x(y)/',
            Grammar::fromArray(['x' => new CapturingRegExp('x(y)', [], 'x')])
        ];
        yield 'Word literal' => [
            'x = `x`',
            Grammar::fromArray(['x' => new Word('x', 'x')])
        ];
        yield 'EOF' => [
            'x = EOF',
            Grammar::fromArray(['x' => new EOF()])
        ];
        yield 'Epsilon' => [
            'x = E',
            Grammar::fromArray(['x' => new Epsilon()])
        ];
        yield 'Fail' => [
            'x = FAIL',
            Grammar::fromArray(['x' => new Fail()])
        ];
        yield 'BackReference' => [
            'x = $x',
            Grammar::fromArray(['x' => new BackReference('x')])
        ];
    }

    /**
     * @dataProvider provideTestItParsesCombinators
     *
     * @param string  $syntax
     * @param Grammar $expected
     */
    public function testItParsesCombinators($syntax, $expected)
    {
        $grammar = $this->parseSyntax($syntax, Optimizer::LEVEL_1);
        $this->assertGrammarEquals($expected, $grammar);
    }

    /**
     * @dataProvider provideTestItParsesCombinators
     *
     * @param string  $syntax
     * @param Grammar $expected
     */
    public function testItParsesCombinatorsWithOptimizedMeta($syntax, $expected)
    {
        $grammar = $this->parseSyntax($syntax, Optimizer::LEVEL_1, true);
        $this->assertGrammarEquals($expected, $grammar);
    }

    public function provideTestItParsesCombinators()
    {
        yield 'Sequence of matches' => [
            'x = /x/ /y/ /z/',
            Grammar::fromArray(['x' => new Sequence([
                new CapturingRegExp('x'),
                new CapturingRegExp('y'),
                new CapturingRegExp('z'),
            ])])
        ];
        yield 'Choice of matches' => [
            'x = /x/ | /y/ | /z/',
            Grammar::fromArray(['x' => new OneOf([
                new CapturingRegExp('x'),
                new CapturingRegExp('y'),
                new CapturingRegExp('z'),
            ])])
        ];
        yield 'NodeAction of matches' => [
            'x = /x/ /y/ /z/ <= XYZ',
            Grammar::fromArray([
                'x' => new NodeAction(new Sequence([
                    new CapturingRegExp('x'),
                    new CapturingRegExp('y'),
                    new CapturingRegExp('z'),
                ]), 'XYZ')
            ])
        ];
        yield 'Choice with named sequence' => [
            'x = /x/ | /y/ <= Y | /z/',
            Grammar::fromArray(['x' => new OneOf([
                new CapturingRegExp('x'),
                new NodeAction(new CapturingRegExp('y'), 'Y'),
                new CapturingRegExp('z'),
            ])])
        ];
    }

    /**
     * @dataProvider provideTestItParsesDecorators
     */
    public function testItParsesDecorators($syntax, $expected)
    {
        $grammar = $this->parseSyntax($syntax, 0);
        $this->assertGrammarEquals($expected, $grammar);
    }

    /**
     * @dataProvider provideTestItParsesDecorators
     *
     * @param string  $syntax
     * @param Grammar $expected
     */
    public function testItParsesDecoratorsWithOptimizedMeta($syntax, $expected)
    {
        $grammar = $this->parseSyntax($syntax, 0, true);
        $this->assertGrammarEquals($expected, $grammar);
    }

    public function provideTestItParsesDecorators()
    {
        yield 'Assert of a match' => [
            'x = &/x/',
            Grammar::fromArray(['x' => new Assert(new CapturingRegExp('x'))])
        ];
        yield 'Not of a match' => [
            'x = !/x/',
            Grammar::fromArray(['x' => new Not(new CapturingRegExp('x'))])
        ];
        yield 'Skip of a match' => [
            'x = ~/x/',
            Grammar::fromArray(['x' => new Ignore(new CapturingRegExp('x'))])
        ];
        yield 'Token of a match' => [
            'x = %/x/',
            Grammar::fromArray(['x' => new Token(new CapturingRegExp('x'))])
        ];
        yield 'Labeled match' => [
            'x = a:/x/',
            Grammar::fromArray(['x' => new Label('a', new CapturingRegExp('x'))])
        ];
        yield 'ZeroOrMore match' => [
            'x = /x/*',
            Grammar::fromArray(['x' => new ZeroOrMore(new CapturingRegExp('x'))])
        ];
        yield 'OneOrMore match' => [
            'x = /x/+',
            Grammar::fromArray(['x' => new OneOrMore(new CapturingRegExp('x'))])
        ];
        yield 'Optional match' => [
            'x = /x/?',
            Grammar::fromArray(['x' => new Optional(new CapturingRegExp('x'))])
        ];
        yield 'Exactly 2 match' => [
            'x = /x/{2}',
            Grammar::fromArray(['x' => new Quantifier(new CapturingRegExp('x'), 2, 2)])
        ];
        yield 'At least 2 match' => [
            'x = /x/{2,}',
            Grammar::fromArray(['x' => new Quantifier(new CapturingRegExp('x'), 2)])
        ];
        yield 'Between 2 and 4 match' => [
            'x = /x/{2,4}',
            Grammar::fromArray(['x' => new Quantifier(new CapturingRegExp('x'), 2, 4)])
        ];
        yield 'Cut operator' => [
            'x = "["^',
            Grammar::fromArray(['x' => new Cut(new Literal('['))])
        ];
        yield 'Cut quantifier' => [
            'x = "X"+^',
            Grammar::fromArray(['x' => new Cut(new OneOrMore(new Literal('X')))])
        ];
    }

    /**
     * @dataProvider provideTestDecoratorPrecedence
     *
     * @param string $syntax
     * @param Grammar $expected
     */
    public function testDecoratorPrecedence(string $syntax, Grammar $expected)
    {
        $grammar = $this->parseSyntax($syntax);
        $this->assertGrammarEquals($expected, $grammar);
    }

    public function provideTestDecoratorPrecedence()
    {
        yield 'ignore and quantifier' => [
            'x = ~"foo"+',
            Grammar::fromArray([
                'x' => new Ignore(new OneOrMore(new Literal('foo'))),
            ])
        ];
        yield 'token and cut' => [
            'x = %"foo"^',
            Grammar::fromArray([
                'x' => new Token(new Cut(new Literal('foo'))),
            ])
        ];
        yield 'cut and quantifier' => [
            'x = "foo"+^',
            Grammar::fromArray([
                'x' => new Cut(new OneOrMore(new Literal('foo'))),
            ])
        ];
        yield 'ignore and token' => [
            'x = ~%"foo"',
            Grammar::fromArray([
                'x' => new Ignore(new Token(new Literal('foo'))),
            ])
        ];
    }

    /**
     * @dataProvider provideTestItParsesReferences
     *
     * @param string  $syntax
     * @param Grammar $expected
     */
    public function testItParsesReferences($syntax, $expected)
    {
        $grammar = $this->parseSyntax($syntax);
        $this->assertGrammarEquals($expected, $grammar);
    }

    /**
     * @dataProvider provideTestItParsesReferences
     *
     * @param string  $syntax
     * @param Grammar $expected
     */
    public function testItParsesReferencesWithOptimizedMeta($syntax, $expected)
    {
        $grammar = $this->parseSyntax($syntax, 0, true);
        $this->assertGrammarEquals($expected, $grammar);
    }

    public function provideTestItParsesReferences()
    {
        yield 'Reference' => [
            'x = y',
            Grammar::fromArray(['x' => new Reference('y')])
        ];
        yield 'Super' => [
            'x = super',
            Grammar::fromArray(['x' => new Super('x')])
        ];
    }

    public function testNameDirective()
    {
        $syntax = "@grammar Foo\nx = y";
        $expected = 'Foo';

        $grammar = $this->parseSyntax($syntax);
        $this->assertSame($expected, $grammar->getName());

        $grammar = $this->parseSyntax($syntax, 0, true);
        $this->assertSame($expected, $grammar->getName(), 'With optimized meta');
    }

    public function testStartDirective()
    {
        $syntax = "@start y\nx = y\ny = z";
        $start = 'y';

        $grammar = $this->parseSyntax($syntax);
        $this->assertSame($start, $grammar->getStartRule());

        $grammar = $this->parseSyntax($syntax, 0, true);
        $this->assertSame($start, $grammar->getStartRule(), 'With optimized meta');
    }

    public function testInlineDirective()
    {
        $syntax = '@inline x = y';
        $start = 'y';

        $grammar = $this->parseSyntax($syntax);
        $this->assertTrue($grammar->isInlined('x'));

        $grammar = $this->parseSyntax($syntax, 0, true);
        $this->assertTrue($grammar->isInlined('x'), 'With optimized meta');
    }
}
