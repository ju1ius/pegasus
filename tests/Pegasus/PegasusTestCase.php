<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Terminal\GroupMatch;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\CST\Node;
use PHPUnit\Framework\TestCase;


/**
 * Class PegasusTestCase
 * @author ju1ius
 */
class PegasusTestCase extends TestCase
{
	/**
	 * Compares two nodes.
     *
     * @param Node $expected
     * @param Node $actual
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
     */
    public function assertGrammarEquals(Grammar $expected, Grammar $actual)
    {
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
        $expr->id = -1;
        if ($expr instanceof Composite) {
            foreach ($expr as $child) {
                $this->cleanupExpr($child);
            }
        } elseif ($expr instanceof GroupMatch) {
            $expr->getMatcher()->id = -1;
        }
    }
}
