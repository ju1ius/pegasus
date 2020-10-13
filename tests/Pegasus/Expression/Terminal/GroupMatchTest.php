<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\Expression\Terminal;

use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Expression\Terminal\GroupMatch;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class GroupMatchTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
     */
    public function testMatch($expr, $matchArgs, $expected)
    {
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$matchArgs)
        );
    }

    public function provideTestMatch()
    {
        yield 'Single capturing group' => [
            ['test' => new GroupMatch(new Match('\s*(\w+)'), 1, 'test')],
            ['   abc123   '],
            new Terminal('test', 0, 9, 'abc123')
        ];
        yield 'Multiple capturing group' => [
            ['test' => new GroupMatch(new Match('\s*(\w+)\s+(\w+)'), 2, 'test')],
            ['   abc 123'],
            new Terminal('test', 0, 10, '   abc 123', [
                'captures' => ['abc', '123']
            ])
        ];
    }
}
