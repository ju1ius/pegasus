<?php declare(strict_types=1);
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
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class ReferenceTest extends ExpressionTestCase
{
    /**
     * @dataProvider getMatchProvider
     *
     * @param string $grammar
     * @param array  $args
     * @param Node   $expected
     */
    public function testMatch($grammar, $args, $expected)
    {
        $this->assertNodeEquals($expected, $this->parse($grammar, ...$args));
    }

    public function getMatchProvider()
    {
        return [
            [
                GrammarBuilder::create()
                    ->rule('test')->ref('foo')
                    ->rule('foo')->literal('foo')
                ->getGrammar(),
                ['foo'],
                new Terminal('foo', 0, 3, 'foo')
            ]
        ];
    }
}
