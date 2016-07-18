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

use ju1ius\Pegasus\Expression\Super;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Terminal;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class SuperTest extends ExpressionTestCase
{
    public function testMetadata()
    {
        $super = new Super('super');
        $this->assertFalse($super->isCapturingDecidable());
    }

    /**
     * @dataProvider getMatchProvider
     *
     * @param Grammar $grammar
     * @param array  $args
     * @param Node   $expected
     */
    public function testMatch($grammar, $args, $expected)
    {
        $parent = Builder::create()
            ->rule('foo')->literal('foo')
            ->rule('bar')->literal('bar')
            ->getGrammar();
        $grammar->setParent($parent);
        $this->assertNodeEquals($expected, $this->parse($grammar, ...$args));
    }

    public function getMatchProvider()
    {
        return [
            [
                Builder::create()
                    ->rule('foo')->oneOf()
                        ->literal('foobar')
                        ->super()
                    ->getGrammar(),
                ['foo'],
                new Node\Decorator('foo', 0, 3, new Terminal('foo', 0, 3, 'foo'))
            ],
            [
                Builder::create()
                    ->rule('foo')->oneOf()
                        ->literal('foobar')
                        ->super('bar')
                    ->getGrammar(),
                ['bar'],
                new Node\Decorator('foo', 0, 3, new Terminal('bar', 0, 3, 'bar'))
            ],
        ];
    }
}
