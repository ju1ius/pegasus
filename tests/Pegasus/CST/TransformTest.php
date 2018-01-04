<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\CST;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Composite;
use ju1ius\Pegasus\CST\Node\Decorator;
use ju1ius\Pegasus\CST\Node\Quantifier;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\CST\Exception\TransformException;
use ju1ius\Pegasus\CST\Transform;
use PHPUnit\Framework\TestCase;


/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class TransformTest extends TestCase
{
    public function testDefaultBeforeAndAfterTraverse()
    {
        $traverser = $this->getMockBuilder(Transform::class)
            ->setMethods(['beforeTraverse', 'afterTraverse'])
            ->getMock();
        $node = new Terminal('', 0, 0);

        $traverser->expects($this->once())
            ->method('beforeTraverse')
            ->with($node)
            ->willReturn(null);

        $traverser->expects($this->once())
            ->method('afterTraverse')
            ->willReturnArgument(0);

        $traverser->transform($node);
    }

    /**
     * @dataProvider getTestDefaultVisitationBehaviorProvider
     *
     * @param Node  $node
     * @param mixed $expected
     */
    public function testDefaultVisitationBehavior(Node $node, $expected)
    {
        $traverser = new Transform();
        $result = $traverser->transform($node);
        $this->assertEquals($expected, $result);
    }

    public function getTestDefaultVisitationBehaviorProvider()
    {
        return [
            'Returns the value of a terminal node' => [
                new Terminal('foo', 0, 3, 'foo'),
                'foo'
            ],
            'Returns the node if a terminal node has a groups attribute.' => [
                new Terminal('foo', 0, 3, 'foo', ['groups' => ['bar', 'baz']]),
                new Terminal('foo', 0, 3, 'foo', ['groups' => ['bar', 'baz']]),
            ],
            'Returns a terminal `captures` attribute if present.' => [
                new Terminal('foo', 0, 3, 'foobar', ['captures' => ['foo', 'bar']]),
                ['foo', 'bar'],
            ],
            'Returns the child of a decorator node' => [
                new Decorator('foo', 0, 3, new Terminal('', 0, 3, 'foo')),
                'foo'
            ],
            'Returns the children of a quantifier node' => [
                new Quantifier('foo', 0, 6, [
                    new Terminal('', 0, 3, 'foo'),
                    new Terminal('', 3, 6, 'foo'),
                ]),
                ['foo', 'foo']
            ],
            'Returns the child of an optional quantifier node' => [
                new Quantifier('foo', 0, 3, [
                    new Terminal('', 0, 3, 'foo'),
                ], true),
                'foo'
            ],
            'Returns null for an optional quantifier node with no children' => [
                new Quantifier('foo', 0, 3, [], true),
                null
            ],
            'Returns the children of a composite node' => [
                new Composite('foobar', 0, 9, [
                    new Terminal('', 0, 3, 'foo'),
                    new Terminal('', 3, 6, 'bar'),
                    new Terminal('', 3, 6, 'baz'),
                ]),
                ['foo', 'bar', 'baz']
            ],
            'Returns a single child for a composite node with one child.' => [
                new Composite('foobar', 0, 3, [
                    new Terminal('', 0, 3, 'foo'),
                ]),
                'foo'
            ],
        ];
    }

    public function testCustomVisitationMethodsAreCalled()
    {
        $traverser = $this->getMockBuilder(Transform::class)
            ->setMethods(['enter_Foo', 'leave_Foo'])
            ->getMock();
        $node = new Terminal('Foo', 0, 3, 'foo');

        $traverser->expects($this->once())
            ->method('enter_Foo')
            ->with($node)
            ->willReturn(null);

        $traverser->expects($this->once())
            ->method('leave_Foo')
            ->with($node)
            ->willReturn('foo');

        $result = $traverser->transform($node);
        $this->assertSame('foo', $result);
    }

    public function testItConvertsExceptionsToVisitationError()
    {
        $traverser = $this->getMockBuilder(Transform::class)
            ->setMethods(['leaveTerminal'])
            ->getMock();

        $traverser->expects($this->any())
            ->method('leaveTerminal')
            ->willThrowException(new \RuntimeException());

        $this->expectException(TransformException::class);

        $node = new Terminal('Foo', 0, 3, 'foo');
        $traverser->transform($node);
    }

    public function testItCanThrowVisitationErrors()
    {
        $traverser = $this->getMockBuilder(Transform::class)
            ->setMethods(['leaveTerminal'])
            ->getMock();
        $node = new Terminal('Foo', 0, 3, 'foo');

        $traverser->expects($this->any())
            ->method('leaveTerminal')
            ->willThrowException(new TransformException($node, $node, 'I fucked up.'));

        $this->expectException(TransformException::class);

        $traverser->transform($node);
    }
}
