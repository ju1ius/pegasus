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
use ju1ius\Pegasus\Tests\ExpressionTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class RegExpTest extends ExpressionTestCase
{
    /**
     * @dataProvider testMatchProvider
     */
    public function testMatch($expr, $match_args, $expected)
    {
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$match_args)
        );
    }
    public function testMatchProvider()
    {
        return [
            // simple literals
            '/foo/ with "foo"' => [
                Builder::create()->rule('r')->regexp('foo')->getGrammar(),
                ['foo'],
                new Node('r', 0, 3, 'foo', [], ['matches' => ['foo']])
            ],
            '/bar/ @3 with "foobar"' => [
                Builder::create()->rule('r')->regexp('bar')->getGrammar(),
                ['foobar', 3],
                new Node('r', 3, 6, 'foobar', [], ['matches' => ['bar']])
            ],
            '/fo+/ with "fooooobar!"' => [
                Builder::create()->rule('r')->regexp('fo+')->getGrammar(),
                ['fooooobar!'],
                new Node('r', 0, 6, 'fooooobar!', [], ['matches' => ['fooooo']])
            ],
            'complex pattern with capturing groups' => [
                Builder::create()->rule('r')->regexp('"((?:(?:\\\\.)|[^"])*)"')->getGrammar(),
                ['"quoted\\"stri\\ng"'],
                new Node('r', 0, 17, '"quoted\\"stri\\ng"', [], ['matches' => [
                    '"quoted\\"stri\\ng"',
                    'quoted\\"stri\\ng'
                ]])
            ],
        ];
    }
}
