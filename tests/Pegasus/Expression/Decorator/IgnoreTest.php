<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\Expression\Decorator;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class IgnoreTest extends ExpressionTestCase
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
                GrammarBuilder::create()->rule('nope')->ignore()->literal('nope')->getGrammar(),
                ['nope'],
                true
            ],
            'skip parenthesis around (foo)' => [
                GrammarBuilder::create()->rule('start')->seq()
                    ->ignore()->literal('(')
                    ->literal('foo')
                    ->ignore()->literal(')')
                    ->getGrammar(),
                ['(foo)'],
                new Terminal('start', 1, 4, 'foo')
            ],
            'skip choice result at sequence start' => [
                GrammarBuilder::create()->rule('start')->seq()
                    ->ignore()->oneOf()
                        ->literal('€')
                        ->literal('$')
                        ->literal('£')
                    ->end()
                    ->literal('42')
                    ->getGrammar(),
                ['$42'],
                new Terminal('start', 1, 3, '42')
            ]
        ];
    }
}
