<?php

namespace ju1ius\Pegasus\Tests;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Node;

/**
 * Class PegasusTestCase
 * @author ju1ius
 */
class PegasusTestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * Compares two nodes.
	 *
	 * @param Node $expected
	 * @param Node $actual
	 *
	 * @return bool
	 */
	public function assertNodeEquals(Node $expected, Node $actual)
	{
		foreach ($expected->iter() as $node) {
			$node->expr = null;
		}
		foreach ($actual->iter() as $node) {
			$node->expr = null;
		}
		$this->assertEquals($expected, $actual);
	}

    /**
     * Asserts that two expression are equals, omitting their id property.
     */
    public function assertExpressionEquals(Expression $expected, Expression $actual)
    {
        $this->cleanupExpr($expected);
        $this->cleanupExpr($actual);
        $this->assertEquals($expected, $actual);
    }

    public function assertGrammarEquals(Grammar $expected, Grammar $actual)
    {
        $expected->unfold();
        $actual->unfold();
        foreach ($expected as $name => $expr) {
            $this->assertArrayHasKey($name, $actual);
            $this->assertExpressionEquals($expr, $actual[$name]);
        }
    }

    protected function cleanupExpr(Expression $expr)
    {
        $expr->id = null;
        if ($expr instanceof Expression\Composite) {
            foreach ($expr as $child) {
                $this->cleanupExpr($child);
            }
        }
    }
}
