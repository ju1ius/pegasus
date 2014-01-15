<?php

namespace ju1ius\Test\Pegasus;

use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Expression;


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
			$node->expr_class = null;
		}
		foreach ($actual->iter() as $node) {
			$node->expr_class = null;
		}
		return $this->assertEquals($expected, $actual);
	}
	
    /**
     * Asserts that two expression are equals, omitting their id property.
     */
    public function assertExpressionEquals(Expression $expected, Expression $actual)
    {
        $unset_id = function ($expr) use (&$unset_id) {
            $expr->id = '';
            if ($expr instanceof Expression\Composite) {
                foreach ($expr->members as $child) {
                    $unset_id($child);   
                }
            }
        };
        $unset_id($expected);
        $unset_id($actual);
        return $this->assertEquals($expected, $actual);
    }
}
