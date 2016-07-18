<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests;

use ju1ius\Pegasus\Node;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class NodeTest extends PegasusTestCase
{
    public function testGetText()
    {
        $input = 'foobarbaz';
        $node = new Node('test', 3, 6, 'bar');
        $this->assertSame('bar', $node->getText($input));
    }

    public function testOffsetSet()
    {
        $node = new Node('', 0, 1);
        $child = new Node('foo', 0, 1, 'bar');
        $node[0] = $child;
        $node['foo'] = 'bar';
        $this->assertSame($child, $node->children[0]);
        $this->assertSame('bar', $node->attributes['foo']);
    }

    public function testOffsetExists()
    {
        $node = new Node('test', 0, 42, null, [
            new Node('', 0, 7, 'foo')
        ]);
        $this->assertTrue(isset($node[0]));
        $this->assertFalse(isset($node[1]));
        $this->assertFalse(isset($node['foo']));

        $node = new Node('test', 0, 42, null, [], [
            'foo' => 'bar'
        ]);
        $this->assertTrue(isset($node['foo']));
        $this->assertFalse(isset($node['bar']));
        $this->assertFalse(isset($node[0]));
    }

    public function testOffsetGet()
    {
        $child = new Node('', 0, 7, 'foo');
        $node = new Node('test', 0, 42, null, [$child], ['foo' => 'bar']);
        $this->assertSame($child, $node[0]);
        $this->assertSame('bar', $node['foo']);
    }

    public function testOffsetUnset()
    {
        $node = new Node('test', 0, 42, null, [
            new Node('', 0, 7, 'foo'),
        ], [
            'foo' => 'bar'
        ]);
        unset($node[0]);
        unset($node['foo']);
        $this->assertFalse(isset($node[0]));
        $this->assertFalse(isset($node['foo']));
    }

    public function testCount()
    {
        $node = new Node('test', 0, 42, null, [
            new Node('', 0, 7, 'foo'),
            new Node('', 7, 14, 'bar'),
        ]);
        $this->assertSame(2, count($node));
    }

    public function testGetIterator()
    {
        $node = new Node('', 0, 1);
        $this->assertInstanceOf(\Traversable::class, $node);
        $this->assertInstanceOf(\Iterator::class, $node->getIterator());
    }
}
