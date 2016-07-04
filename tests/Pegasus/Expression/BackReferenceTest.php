<?php

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\Exception\UndefinedBinding;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

/**
 * @author ju1ius
 */
class BackReferenceTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
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

    public function testMatchProvider()
    {
        return [
            [
                Builder::create()
                    ->rule('start')->seq()
                        ->label('a')->literal('foo')
                        ->literal('bar')
                        ->backref('a')
                    ->getGrammar(),
                ['foobarfoo'],
                new Node('start', 0, 9, 'foobarfoo', [
                    new Node('', 0, 3, 'foobarfoo'),
                    new Node('', 3, 6, 'foobarfoo'),
                    new Node('', 6, 9, 'foobarfoo'),
                ])
            ]
        ];
    }

    /**
     * @param       $grammar
     * @param array $params
     *
     * @dataProvider testOutOfScopeReferenceProvider
     */
    public function testOutOfScopeReference($grammar, array $params)
    {
        $this->expectException(UndefinedBinding::class);
        $this->parse($grammar, ...$params);
    }

    public function testOutOfScopeReferenceProvider()
    {
        return [
            'reference in another rule' => [
                Builder::create()
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
