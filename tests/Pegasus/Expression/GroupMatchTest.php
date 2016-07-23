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

use ju1ius\Pegasus\Expression\Terminal\GroupMatch;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\CST\Node\Composite;
use ju1ius\Pegasus\CST\Node\Decorator;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class GroupMatchTest extends ExpressionTestCase
{
    /**
     * @dataProvider getMatchProvider
     */
    public function testMatch($expr, $matchArgs, $expected)
    {
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$matchArgs)
        );
    }

    public function getMatchProvider()
    {
        return [
            'Single capturing group' => [
                ['test' => new GroupMatch(new Match('\s*(\w+)'), 1, 'test')],
                ['   abc123   '],
                new Decorator('test', 0, 9, new Terminal('', 3, 9, 'abc123'))
            ],
            'Multiple capturing group' => [
                ['test' => new GroupMatch(new Match('\s*(\w+)\s+(\w+)'), 2, 'test')],
                ['   abc 123'],
                new Composite('test', 0, 10, [
                    new Terminal('', 3, 6, 'abc'),
                    new Terminal('', 7, 10, '123'),
                ])
            ]
        ];
    }
}
