<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class SkipTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     *
     * @param Grammar $grammar
     * @param array   $args
     * @param Node    $expected
     */
    public function testMatch(Grammar $grammar, array $args, Node $expected)
    {
        $node = $this->parse($grammar, ...$args);
        $this->assertNodeEquals($expected, $node);
    }

    public function testMatchProvider()
    {
        return [
            'produces a non-capturing node, with the correct positions' => [
                Builder::create()->rule('nope')->skip()->literal('nope')->getGrammar(),
                ['nope'],
                Node::transient('nope', 0, 4)
            ],
            'skip parenthesis around (foo)' => [
                Builder::create()->rule('start')->seq()
                    ->skip()->literal('(')
                    ->literal('foo')
                    ->skip()->literal(')')
                    ->getGrammar(),
                ['(foo)'],
                new Node('start', 0, 5, null, [
                    new Node('', 1, 4, 'foo')
                ])
            ],
            'skip choice result at sequence start' => [
                Builder::create()->rule('start')->seq()
                    ->skip()->oneOf()
                        ->literal('€')
                        ->literal('$')
                        ->literal('£')
                    ->end()
                    ->literal('42')
                    ->getGrammar(),
                ['$42'],
                new Node('start', 0, 3, null, [
                    new Node('', 1, 3, '42')
                ])
            ]
        ];
    }
}
