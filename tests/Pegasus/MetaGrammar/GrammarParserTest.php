<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\MetaGrammar;

use ju1ius\Pegasus\Expression\Decorator\NodeAction;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Decorator\Assert;
use ju1ius\Pegasus\Expression\Decorator\Label;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\Decorator\OneOrMore;
use ju1ius\Pegasus\Expression\Decorator\Optional;
use ju1ius\Pegasus\Expression\Decorator\Quantifier;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Decorator\Token;
use ju1ius\Pegasus\Expression\Decorator\ZeroOrMore;
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Expression\Application\Super;
use ju1ius\Pegasus\Expression\Terminal\BackReference;
use ju1ius\Pegasus\Expression\Terminal\EOF;
use ju1ius\Pegasus\Expression\Terminal\Epsilon;
use ju1ius\Pegasus\Expression\Terminal\Fail;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\Expression\Terminal\RegExp;
use ju1ius\Pegasus\Expression\Terminal\Word;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimizer;
use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Parser\LeftRecursivePackrat;
use ju1ius\Pegasus\Tests\PegasusTestCase;
use ju1ius\Pegasus\MetaGrammar\MetaGrammarTransform;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class GrammarParserTest extends PegasusTestCase
{
    private function parseSyntax($syntax, $optimizationLevel = 0, $optimizedMeta = false)
    {
        $meta = $optimizedMeta ? MetaGrammar::create() : MetaGrammar::getGrammar();
        $parser = new LeftRecursivePackrat($meta);
        try {
            $tree = $parser->parseAll($syntax);
        } catch (ParseError $err) {
            $this->fail($err);
        }
        $grammar = (new MetaGrammarTransform())->transform($tree);
        if ($optimizationLevel) {
            return Optimizer::optimize($grammar, $optimizationLevel);
        }

        return $grammar;
    }

    /**
     * @dataProvider getTestItParsesTerminalRulesProvider
     *
     * @param string  $syntax
     * @param Grammar $expected
     */
    public function testItParsesTerminalRules($syntax, $expected)
    {
        $grammar = $this->parseSyntax($syntax);
        $this->assertGrammarEquals($expected, $grammar);
    }

    /**
     * @dataProvider getTestItParsesTerminalRulesProvider
     *
     * @param string  $syntax
     * @param Grammar $expected
     */
    public function testItParsesTerminalRulesWithOptimizedMeta($syntax, $expected)
    {
        $grammar = $this->parseSyntax($syntax, 0, true);
        $this->assertGrammarEquals($expected, $grammar);
    }

    public function getTestItParsesTerminalRulesProvider()
    {
        return [
            'double-quoted literal' => [
                'x = "x"',
                Grammar::fromArray(['x' => new Literal('x', 'x', '"')])
            ],
            'single-quoted literal' => [
                "x = 'x'",
                Grammar::fromArray(['x' => new Literal('x', 'x', "'")])
            ],
            'match' => [
                'x = /x/',
                Grammar::fromArray(['x' => new Match('x', [], 'x')])
            ],
            'match with flags' => [
                'x = /x/i',
                Grammar::fromArray(['x' => new Match('x', ['i'], 'x')])
            ],
            'match non-capturing groups' => [
                'x = /(?:x)(?!y)/',
                Grammar::fromArray(['x' => new Match('(?:x)(?!y)', [], 'x')])
            ],
            'RegExp' => [
                'x = /x(y)/',
                Grammar::fromArray(['x' => new RegExp('x(y)', [], 'x')])
            ],
            'Word literal' => [
                'x = `x`',
                Grammar::fromArray(['x' => new Word('x', 'x')])
            ],
            'EOF' => [
                'x = EOF',
                Grammar::fromArray(['x' => new EOF()])
            ],
            'Epsilon' => [
                'x = E',
                Grammar::fromArray(['x' => new Epsilon()])
            ],
            'Fail' => [
                'x = FAIL',
                Grammar::fromArray(['x' => new Fail()])
            ],
            'BackReference' => [
                'x = $x',
                Grammar::fromArray(['x' => new BackReference('x')])
            ],
        ];
    }

    /**
     * @dataProvider getTestItParsesCombinatorsProvider
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
     * @dataProvider getTestItParsesCombinatorsProvider
     *
     * @param string  $syntax
     * @param Grammar $expected
     */
    public function testItParsesCombinatorsWithOptimizedMeta($syntax, $expected)
    {
        $grammar = $this->parseSyntax($syntax, Optimizer::LEVEL_1, true);
        $this->assertGrammarEquals($expected, $grammar);
    }

    public function getTestItParsesCombinatorsProvider()
    {
        return [
            'Sequence of matches' => [
                'x = /x/ /y/ /z/',
                Grammar::fromArray(['x' => new Sequence([
                    new Match('x'),
                    new Match('y'),
                    new Match('z'),
                ])])
            ],
            'Choice of matches' => [
                'x = /x/ | /y/ | /z/',
                Grammar::fromArray(['x' => new OneOf([
                    new Match('x'),
                    new Match('y'),
                    new Match('z'),
                ])])
            ],
            'NodeAction of matches' => [
                'x = /x/ /y/ /z/ <= XYZ',
                Grammar::fromArray([
                    'x' => new NodeAction(new Sequence([
                        new Match('x'),
                        new Match('y'),
                        new Match('z'),
                    ]), 'XYZ')
                ])
            ],
            'Choice with named sequence' => [
                'x = /x/ | /y/ <= Y | /z/',
                Grammar::fromArray(['x' => new OneOf([
                    new Match('x'),
                    new NodeAction(new Match('y'), 'Y'),
                    new Match('z'),
                ])])
            ],
        ];
    }

    /**
     * @dataProvider getTestItParsesDecoratorsProvider
     */
    public function testItParsesDecorators($syntax, $expected)
    {
        $grammar = $this->parseSyntax($syntax, 0);
        $this->assertGrammarEquals($expected, $grammar);
    }

    /**
     * @dataProvider getTestItParsesDecoratorsProvider
     *
     * @param string  $syntax
     * @param Grammar $expected
     */
    public function testItParsesDecoratorsWithOptimizedMeta($syntax, $expected)
    {
        $grammar = $this->parseSyntax($syntax, 0, true);
        $this->assertGrammarEquals($expected, $grammar);
    }

    public function getTestItParsesDecoratorsProvider()
    {
        return [
            'Assert of a match' => [
                'x = &/x/',
                Grammar::fromArray(['x' => new Assert(new Match('x'))])
            ],
            'Not of a match' => [
                'x = !/x/',
                Grammar::fromArray(['x' => new Not(new Match('x'))])
            ],
            'Skip of a match' => [
                'x = ~/x/',
                Grammar::fromArray(['x' => new Ignore(new Match('x'))])
            ],
            'Token of a match' => [
                'x = @/x/',
                Grammar::fromArray(['x' => new Token(new Match('x'))])
            ],
            'Labeled match' => [
                'x = a:/x/',
                Grammar::fromArray(['x' => new Label(new Match('x'), 'a')])
            ],
            'ZeroOrMore match' => [
                'x = /x/*',
                Grammar::fromArray(['x' => new ZeroOrMore(new Match('x'))])
            ],
            'OneOrMore match' => [
                'x = /x/+',
                Grammar::fromArray(['x' => new OneOrMore(new Match('x'))])
            ],
            'Optional match' => [
                'x = /x/?',
                Grammar::fromArray(['x' => new Optional(new Match('x'))])
            ],
            'Exactly 2 match' => [
                'x = /x/{2}',
                Grammar::fromArray(['x' => new Quantifier(new Match('x'), 2, 2)])
            ],
            'At least 2 match' => [
                'x = /x/{2,}',
                Grammar::fromArray(['x' => new Quantifier(new Match('x'), 2)])
            ],
            'Between 2 and 4 match' => [
                'x = /x/{2,4}',
                Grammar::fromArray(['x' => new Quantifier(new Match('x'), 2, 4)])
            ],
        ];
    }

    /**
     * @dataProvider getTestItParsesReferencesProvider
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
     * @dataProvider getTestItParsesReferencesProvider
     *
     * @param string  $syntax
     * @param Grammar $expected
     */
    public function testItParsesReferencesWithOptimizedMeta($syntax, $expected)
    {
        $grammar = $this->parseSyntax($syntax, 0, true);
        $this->assertGrammarEquals($expected, $grammar);
    }

    public function getTestItParsesReferencesProvider()
    {
        return [
            'Reference' => [
                'x = y',
                Grammar::fromArray(['x' => new Reference('y')])
            ],
            'Super' => [
                'x = super',
                Grammar::fromArray(['x' => new Super('x')])
            ],
        ];
    }

    public function testNameDirective()
    {
        $syntax = "%name Foo\nx = y";
        $expected = 'Foo';

        $grammar = $this->parseSyntax($syntax);
        $this->assertSame($expected, $grammar->getName());

        $grammar = $this->parseSyntax($syntax, 0, true);
        $this->assertSame($expected, $grammar->getName(), 'With optimized meta');
    }

    public function testStartDirective()
    {
        $syntax = "%start y\nx = y\ny = z";
        $start = 'y';

        $grammar = $this->parseSyntax($syntax);
        $this->assertSame($start, $grammar->getStartRule());

        $grammar = $this->parseSyntax($syntax, 0, true);
        $this->assertSame($start, $grammar->getStartRule(), 'With optimized meta');
    }

    public function testInlineDirective()
    {
        $syntax = '%inline x = y';
        $start = 'y';

        $grammar = $this->parseSyntax($syntax);
        $this->assertTrue($grammar->isInlined('x'));

        $grammar = $this->parseSyntax($syntax, 0, true);
        $this->assertTrue($grammar->isInlined('x'), 'With optimized meta');
    }
}
