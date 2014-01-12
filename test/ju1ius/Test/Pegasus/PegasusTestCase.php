<?php

namespace ju1ius\Test\Pegasus;

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
			$node->expr_class = null;
		}
		foreach ($actual->iter() as $node) {
			$node->expr_class = null;
		}
		return $this->assertEquals($expected, $actual);
	}
	
}
