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
use ju1ius\Pegasus\Tests\ExpressionTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class RegExpTest extends ExpressionTestCase
{
    /**
     * @dataProvider getMatchProvider
     */
    public function testMatch($expr, $match_args, $expected)
    {
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$match_args)
        );
    }
    public function getMatchProvider()
    {
        return [
            // simple literals
            '/foo/ with "foo"' => [
                Builder::create()->rule('r')->regexp('foo')->getGrammar(),
                ['foo'],
                new Terminal('r', 0, 3, 'foo', ['matches' => ['foo']])
            ],
            '/bar/ @3 with "foobar"' => [
                Builder::create()->rule('r')->regexp('bar')->getGrammar(),
                ['foobar', 3],
                new Terminal('r', 3, 6, 'bar', ['matches' => ['bar']])
            ],
            '/fo+/ with "fooooobar!"' => [
                Builder::create()->rule('r')->regexp('fo+')->getGrammar(),
                ['fooooobar!'],
                new Terminal('r', 0, 6, 'fooooo', ['matches' => ['fooooo']])
            ],
            'complex pattern with capturing groups' => [
                Builder::create()->rule('r')->regexp('"((?:\\\\.|[^"])*)"')->getGrammar(),
                ['"quoted\\"stri\\ng"'],
                new Terminal('r', 0, 17, '"quoted\\"stri\\ng"', ['matches' => [
                    '"quoted\\"stri\\ng"',
                    'quoted\\"stri\\ng'
                ]])
            ],
        ];
    }
}
