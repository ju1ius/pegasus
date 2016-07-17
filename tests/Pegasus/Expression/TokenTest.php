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

use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Terminal;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class TokenTest extends ExpressionTestCase
{
    /**
     * @dataProvider getMatchProvider
     *
     * @param       $grammar
     * @param array $params
     * @param Node  $expected
     */
    public function testMatch($grammar, array $params, $expected)
    {
        $this->assertParseResult($expected, $grammar, ...$params);
    }

    public function getMatchProvider()
    {
        return [
            "Returns the entire string match by it's child" => [
                Builder::create()->rule('test')->token()
                    ->sequence()
                        ->match('\w+')
                        ->match('=')
                        ->match('\d+')
                    ->getGrammar(),
                ['foo=42'],
                new Terminal('test', 0, 6, 'foo=42')
            ],
            "Even if the child is non-capturing" => [
                Builder::create()->rule('test')->token()
                    ->skip()->match('\w+')
                    ->getGrammar(),
                ['foo_bar'],
                new Terminal('test', 0, 7, 'foo_bar')
            ],
            "Should fail if the child fails" => [
                Builder::create()->rule('test')->token()
                    ->match('[a-z]+')
                    ->getGrammar(),
                ['666'],
                null
            ],
        ];
    }
}
