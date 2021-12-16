<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Terminal;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Composite;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Parser\Exception\UndefinedBinding;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

/**
 * @author ju1ius
 */
class BackReferenceTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
     */
    public function testMatch(Grammar $grammar, array $params, Node $expected)
    {
        $result = $this->parse($grammar, ...$params);
        $this->assertNodeEquals($expected, $result);
    }

    public function provideTestMatch(): iterable
    {
        yield 'BackReference to a label in scope' => [
            GrammarBuilder::create()
                ->rule('start')->seq()
                    ->label('a')->literal('foo')
                    ->literal('bar')
                    ->backref('a')
                ->getGrammar(),
            ['foobarfoo'],
            new Composite('start', 0, 9, [
                new Terminal('', 0, 3, 'foo'),
                new Terminal('', 3, 6, 'bar'),
                new Terminal('', 6, 9, 'foo'),
            ])
        ];
        yield 'Scope is not overwritten by rule applications' => [
            GrammarBuilder::create()
                ->rule('a')->sequence()
                    ->label('a')->literal('a')
                    ->ref('b')
                    ->backref('a')
                ->rule('b')->sequence()
                    ->label('a')->literal('b')
                    ->literal('c')
                    ->backref('a')
                ->getGrammar(),
            ['abcba'],
            new Composite('a', 0, 5, [
                new Terminal('', 0, 1, 'a'),
                new Composite('b', 1, 4, [
                    new Terminal('', 1, 2, 'b'),
                    new Terminal('', 2, 3, 'c'),
                    new Terminal('', 3, 4, 'b'),
                ]),
                new Terminal('', 4, 5, 'a'),
            ])
        ];
    }

    /**
     * @dataProvider provideTestOutOfScopeReference
     */
    public function testOutOfScopeReference(Grammar $grammar, array $params)
    {
        $this->expectException(UndefinedBinding::class);
        $this->parse($grammar, ...$params);
    }

    public function provideTestOutOfScopeReference(): iterable
    {
        yield 'reference in another rule' => [
            GrammarBuilder::create()
                ->rule('foobarfoo')->seq()
                    ->ref('foo')->ref('bar')->backref('a')
                ->rule('foo')
                    ->label('a')->literal('foo')
                ->rule('bar')
                    ->literal('bar')
                ->getGrammar(),
            ['foobarfoo']
        ];
    }
}
