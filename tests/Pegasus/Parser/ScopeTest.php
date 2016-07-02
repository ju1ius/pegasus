<?php

namespace ju1ius\Pegasus\Tests\Parser;

use ju1ius\Pegasus\Parser\Scope;
use ju1ius\Pegasus\Tests\PegasusTestCase;

/**
 * @author ju1ius
 */
class ScopeTest extends PegasusTestCase
{
    public function testEmptyScope()
    {
        $scope = Scope::void();
        $this->assertEmpty($scope->getBindings());
        $this->assertEmpty($scope->getCaptures());
    }

    public function testBind()
    {
        $scope = new Scope(['a' => 'foo'], ['foo', 'bar']);
        $newScope = $scope->bind(['b' => 'bar']);
        $this->assertInstanceOf(Scope::class, $newScope);
        $this->assertNotSame($scope, $newScope);
        $this->assertEquals(['a' => 'foo', 'b' => 'bar'], $newScope->getBindings());
        $this->assertEquals(['foo', 'bar'], $newScope->getCaptures(), 'New scope should include existing captures');

        $newScope = $newScope->bind(['b' => 'qux']);
        $this->assertEquals(
            ['a' => 'foo', 'b' => 'qux'],
            $newScope->getBindings(),
            'New scope  should override previous bindings'
        );
    }

    public function testCapture()
    {
        $scope = new Scope(['a' => 'foo'], ['foo', 'bar']);
        $newScope = $scope->capture('baz', 'qux');
        $this->assertInstanceOf(Scope::class, $newScope);
        $this->assertNotSame($scope, $newScope);
        $this->assertEquals(['foo', 'bar', 'baz', 'qux'], $newScope->getCaptures());
        $this->assertEquals(['a' => 'foo'], $newScope->getBindings(), 'New scope should include existing bindings');
    }

    public function testNest()
    {
        $scope = new Scope(['a' => 'foo'], ['foo', 'bar']);
        $newScope = $scope->nest();
        $this->assertInstanceOf(Scope::class, $newScope);
        $this->assertNotSame($scope, $newScope);
        $this->assertEquals(['a' => 'foo'], $newScope->getBindings(), 'New scope should include existing bindings');
        $this->assertEmpty($newScope->getCaptures(), 'New scope should not include existing captures');
    }
}
