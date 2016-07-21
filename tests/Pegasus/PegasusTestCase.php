<?php

namespace ju1ius\Pegasus\Tests;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Terminal\GroupMatch;
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
		$this->assertEquals($expected, $actual);
	}

    /**
     * Asserts that two expression are equals, omitting their id property.
     *
     * @param Expression $expected
     * @param Expression $actual
     * @param string     $msg
     */
    public function assertExpressionEquals(Expression $expected, Expression $actual, $msg = '')
    {
        $this->cleanupExpr($expected);
        $this->cleanupExpr($actual);
        $this->assertEquals($expected, $actual, $msg);
    }

    /**
     * @param Grammar $expected
     * @param Grammar $actual
     * @param bool    $unfold
     */
    public function assertGrammarEquals(Grammar $expected, Grammar $actual, $unfold = false)
    {
        if ($unfold) {
            $expected->unfold();
            $actual->unfold();
        }
        foreach ($expected as $name => $expr) {
            $this->assertArrayHasKey($name, $actual);
            $this->assertExpressionEquals($expr, $actual[$name]);
        }
    }

    /**
     * @param Expression $expr
     */
    protected function cleanupExpr(Expression $expr)
    {
        $expr->id = null;
        if ($expr instanceof Composite) {
            foreach ($expr as $child) {
                $this->cleanupExpr($child);
            }
        } elseif ($expr instanceof GroupMatch) {
            $expr->getMatcher()->id = null;
        }
    }
}
