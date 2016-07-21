<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Composite;
use ju1ius\Pegasus\Node\Terminal;
use ju1ius\Pegasus\Parser\Exception\UndefinedBinding;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

/**
 * @author ju1ius
 */
class BackReferenceTest extends ExpressionTestCase
{
    /**
     * @dataProvider getMatchProvider
     *
     * @param       $grammar
     * @param array $params
     * @param Node  $expected
     */
    public function testMatch($grammar, array $params, Node $expected)
    {
        $result = $this->parse($grammar, ...$params);
        $this->assertNodeEquals($expected, $result);
    }

    public function getMatchProvider()
    {
        return [
            [
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
            ]
        ];
    }

    /**
     * @param       $grammar
     * @param array $params
     *
     * @dataProvider getOutOfScopeReferenceProvider
     */
    public function testOutOfScopeReference($grammar, array $params)
    {
        $this->expectException(UndefinedBinding::class);
        $this->parse($grammar, ...$params);
    }

    public function getOutOfScopeReferenceProvider()
    {
        return [
            'reference in another rule' => [
                GrammarBuilder::create()
                    ->rule('foobarfoo')->seq()
                        ->ref('foo')->ref('bar')->backref('a')
                    ->rule('foo')
                        ->label('a')->literal('foo')
                    ->rule('bar')
                        ->literal('bar')
                    ->getGrammar(),
                ['foobarfoo']
            ],
        ];
    }
}
