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
class TokenTest extends ExpressionTestCase
{
    /**
     * @dataProvider getMatchProvider
     *
     * @param Grammar $grammar
     * @param array   $params
     * @param Node    $expected
     */
    public function testMatch($grammar, array $params, $expected)
    {
        $this->assertParseResult($expected, $grammar, ...$params);
    }

    public function getMatchProvider()
    {
        return [
            "Returns the entire string match by it's child" => [
                GrammarBuilder::create()->rule('test')->token()
                    ->sequence()
                        ->match('\w+')
                        ->match('=')
                        ->match('\d+')
                    ->getGrammar(),
                ['foo=42'],
                new Terminal('test', 0, 6, 'foo=42')
            ],
            "Even if the child is non-capturing" => [
                GrammarBuilder::create()->rule('test')->token()
                    ->ignore()->match('\w+')
                    ->getGrammar(),
                ['foo_bar'],
                new Terminal('test', 0, 7, 'foo_bar')
            ],
            "Should fail if the child fails" => [
                GrammarBuilder::create()->rule('test')->token()
                    ->match('[a-z]+')
                    ->getGrammar(),
                ['666'],
                null
            ],
        ];
    }
}
