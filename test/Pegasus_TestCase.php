<?php

require_once __DIR__.'/../vendor/autoload.php';

use ju1ius\Pegasus\Node;


/**
 * Class Pegasus_TestCase
 * @author ju1ius
 */
class Pegasus_TestCase extends PHPUnit_Framework_TestCase
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
