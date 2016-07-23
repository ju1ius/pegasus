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
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Composite;
use ju1ius\Pegasus\CST\Node\Decorator;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class SkipTest extends ExpressionTestCase
{
    /**
     * @dataProvider getMatchProvider
     *
     * @param Grammar   $grammar
     * @param array     $args
     * @param Node|bool $expected
     */
    public function testMatch(Grammar $grammar, array $args, $expected)
    {
        $result = $this->parse($grammar, ...$args);
        if ($expected instanceof Node) {
            $this->assertNodeEquals($expected, $result);
        } else {
            $this->assertSame($expected, $result);
        }
    }

    public function getMatchProvider()
    {
        return [
            'returns true' => [
                GrammarBuilder::create()->rule('nope')->skip()->literal('nope')->getGrammar(),
                ['nope'],
                true
            ],
            'skip parenthesis around (foo)' => [
                GrammarBuilder::create()->rule('start')->seq()
                    ->skip()->literal('(')
                    ->literal('foo')
                    ->skip()->literal(')')
                    ->getGrammar(),
                ['(foo)'],
                new Decorator('start', 0, 5, new Terminal('', 1, 4, 'foo'))
            ],
            'skip choice result at sequence start' => [
                GrammarBuilder::create()->rule('start')->seq()
                    ->skip()->oneOf()
                        ->literal('€')
                        ->literal('$')
                        ->literal('£')
                    ->end()
                    ->literal('42')
                    ->getGrammar(),
                ['$42'],
                new Decorator('start', 0, 3, new Terminal('', 1, 3, '42'))
            ]
        ];
    }
}
