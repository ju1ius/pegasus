<?php

namespace ju1ius\Pegasus\Tests\Grammar;

use ju1ius\Pegasus\Expression\Assert;
use ju1ius\Pegasus\Expression\BackReference;
use ju1ius\Pegasus\Expression\EOF;
use ju1ius\Pegasus\Expression\Epsilon;
use ju1ius\Pegasus\Expression\Fail;
use ju1ius\Pegasus\Expression\Label;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Expression\NamedSequence;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\Reference as Ref;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Expression\RegExp;
use ju1ius\Pegasus\Expression\Sequence as Seq;
use ju1ius\Pegasus\Expression\Skip;
use ju1ius\Pegasus\Expression\Token;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Tests\PegasusTestCase;

class BuilderTest extends PegasusTestCase
{
    /**
     * @dataProvider getItCanBuildSimpleRulesProvider
     */
    public function testItCanBuildSimpleRules($input, $expected)
    {
        $actual = Grammar::fromArray($input);
        $this->assertGrammarEquals($expected, $actual);
    }
    public function getItCanBuildSimpleRulesProvider()
    {
        return [
            // Terminals
            'Literal' => [
                ['start' => new Literal('foo')],
                Builder::create()->rule('start')->literal('foo')->getGrammar()
            ],
            'Match' => [
                ['start' => new Match('foo', ['i'])],
                Builder::create()->rule('start')->match('foo', ['i'])->getGrammar()
            ],
            'RegExp' => [
                ['start' => new RegExp('foo(bar)', ['i'])],
                Builder::create()->rule('start')->regexp('foo(bar)', ['i'])->getGrammar()
            ],
            'Reference' => [
                ['start' => new Reference('foo')],
                Builder::create()->rule('start')->ref('foo')->getGrammar()
            ],
            'BackReference' => [
                ['start' => new BackReference('foo')],
                Builder::create()->rule('start')->backref('foo')->getGrammar()
            ],
            'EOF' => [
                ['start' => new EOF()],
                Builder::create()->rule('start')->eof()->getGrammar()
            ],
            'Epsilon' => [
                ['start' => new Epsilon()],
                Builder::create()->rule('start')->epsilon()->getGrammar()
            ],
            'Fail' => [
                ['start' => new Fail()],
                Builder::create()->rule('start')->fail()->getGrammar()
            ],
            // Predicates
            'Assert' => [
                ['start' => new Assert(new Literal('foo'))],
                Builder::create()->rule('start')->assert()->literal('foo')->getGrammar()
            ],
            'Not' => [
                ['start' => new Not(new Literal('foo'))],
                Builder::create()->rule('start')->not()->literal('foo')->getGrammar()
            ],
            // Composites
            'Sequence' => [
                ['start' => new Seq([new Literal('foo'), new Ref('bar')])],
                Builder::create()->rule('start')->seq()
                    ->literal('foo')
                    ->ref('bar')
                    ->getGrammar()
            ],
            'Named Sequence' => [
                ['start' => new NamedSequence([new Literal('foo'), new Literal('bar')], 'FooBar')],
                Builder::create()->rule('start')->named('FooBar')
                    ->literal('foo')
                    ->literal('bar')
                    ->getGrammar()
            ],
            'Choice' => [
                ['start' => new OneOf([new Literal('bar'), new Literal('baz')])],
                Builder::create()->rule('start')->oneOf()
                    ->literal('bar')
                    ->literal('baz')
                    ->getGrammar()
            ],
            'Choice of sequences' => [
                [
                    'start' => new OneOf([
                        new Seq([new Literal('foo'), new Literal('bar')]),
                        new Seq([new Ref('baz'), new Ref('qux')]),
                    ])
                ],
                Builder::create()->rule('start')->oneOf()
                    ->seq()->literal('foo')->literal('bar')->end()
                    ->seq()->ref('baz')->ref('qux')->end()
                    ->getGrammar()
            ],
            'Sequence of choices' => [
                [
                    'start' => new Seq([
                        new OneOf([new Literal('foo'), new Literal('bar')]),
                        new OneOf([new Ref('baz'), new Ref('qux')]),
                    ])
                ],
                Builder::create()->rule('start')->seq()
                    ->oneOf()->literal('foo')->literal('bar')->end()
                    ->oneOf()->ref('baz')->ref('qux')->end()
                    ->getGrammar()
            ],
            // Decorators
            'Top-level decorator' => [
                ['start' => new Quantifier(new Literal('foo'), 1, INF)],
                Builder::create()->rule('start')->q(1)->literal('foo')->getGrammar()
            ],
            'Nested decorators' => [
                ['start' => new Not(new Quantifier(new Literal('foo'), 1, 1))],
                Builder::create()->rule('start')->not()->exactly(1)->literal('foo')->getGrammar()
            ],
            'Quantifiers' => [
                [
                    'start' => new Seq([
                        new Quantifier(new Literal('foo'), 1, INF),
                        new Quantifier(new Literal('bar'), 1, 1),
                        new Quantifier(new Literal('baz'), 2, 42),
                    ])
                ],
                Builder::create()->rule('start')->seq()
                    ->q(1)->literal('foo')
                    ->exactly(1)->literal('bar')
                    ->q(2, 42)->literal('baz')
                    ->getGrammar()
            ],
            'Label' => [
                ['start' => new Label(new Literal('foo'), 'a')],
                Builder::create()->rule('start')->label('a')->literal('foo')->getGrammar()
            ],
            'Skip' => [
                ['start' => new Skip(new Literal('foo'))],
                Builder::create()->rule('start')->skip()->literal('foo')->getGrammar()
            ],
            'Token' => [
                ['start' => new Token(new Literal('foo'))],
                Builder::create()->rule('start')->token()->literal('foo')->getGrammar()
            ]
        ];
    }
}
