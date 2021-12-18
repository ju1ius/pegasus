<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\CST;

use ju1ius\Pegasus\CST\Exception\TransformException;
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Composite;
use ju1ius\Pegasus\CST\Node\Decorator;
use ju1ius\Pegasus\CST\Node\Quantifier;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\CST\Transform;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class TransformTest extends TestCase
{
    public function testDefaultBeforeAndAfterTraverse()
    {
        $mock = new class extends Transform {
            public bool $beforeCalled = false;
            public bool $afterCalled = false;
            protected function beforeTraverse(Node $node)
            {
                $this->beforeCalled = true;
                $default = parent::beforeTraverse($node);
                Assert::assertNull($default);
                return $default;
            }
            protected function afterTraverse($node)
            {
                $this->afterCalled = true;
                $default = parent::afterTraverse($node);
                Assert::assertSame($node, $default);
                return $default;
            }
        };
        $node = new Terminal('', 0, 0);
        $mock->transform($node);
        Assert::assertTrue($mock->beforeCalled, 'Transform::beforeTraverse was called');
        Assert::assertTrue($mock->afterCalled, 'Transform::afterTraverse was called');
    }

    /**
     * @dataProvider provideTestDefaultVisitationBehavior
     */
    public function testDefaultVisitationBehavior(Node $node, mixed $expected)
    {
        $traverser = new Transform();
        $result = $traverser->transform($node);
        Assert::assertEquals($expected, $result);
    }

    public function provideTestDefaultVisitationBehavior(): \Traversable
    {
        yield 'Returns the value of a terminal node' => [
            new Terminal('foo', 0, 3, 'foo'),
            'foo'
        ];
        yield 'Returns the node if a terminal node has a groups attribute.' => [
            new Terminal('foo', 0, 3, 'foo', ['groups' => ['bar', 'baz']]),
            new Terminal('foo', 0, 3, 'foo', ['groups' => ['bar', 'baz']]),
        ];
        yield 'Returns a terminal `captures` attribute if present.' => [
            new Terminal('foo', 0, 3, 'foobar', ['captures' => ['foo', 'bar']]),
            ['foo', 'bar'],
        ];
        yield 'Returns the child of a decorator node' => [
            new Decorator('foo', 0, 3, new Terminal('', 0, 3, 'foo')),
            'foo'
        ];
        yield 'Returns the children of a quantifier node' => [
            new Quantifier('foo', 0, 6, [
                new Terminal('', 0, 3, 'foo'),
                new Terminal('', 3, 6, 'foo'),
            ]),
            ['foo', 'foo']
        ];
        yield 'Returns the child of an optional quantifier node' => [
            new Quantifier('foo', 0, 3, [
                new Terminal('', 0, 3, 'foo'),
            ], true),
            'foo'
        ];
        yield 'Returns null for an optional quantifier node with no children' => [
            new Quantifier('foo', 0, 3, [], true),
            null
        ];
        yield 'Returns the children of a composite node' => [
            new Composite('foobar', 0, 9, [
                new Terminal('', 0, 3, 'foo'),
                new Terminal('', 3, 6, 'bar'),
                new Terminal('', 3, 6, 'baz'),
            ]),
            ['foo', 'bar', 'baz']
        ];
        yield 'Returns a single child for a composite node with one child.' => [
            new Composite('foobar', 0, 3, [
                new Terminal('', 0, 3, 'foo'),
            ]),
            'foo'
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
        Assert::assertSame('foo', $result);
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
