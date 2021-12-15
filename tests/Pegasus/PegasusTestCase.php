<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Terminal\GroupMatch;
use ju1ius\Pegasus\Grammar;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * Class PegasusTestCase
 * @author ju1ius
 */
class PegasusTestCase extends TestCase
{
	/**
	 * Compares two nodes.
	 */
	public function assertNodeEquals(Node $expected, Node $actual, string $message = '')
	{
		Assert::assertEquals($expected, $actual, $message);
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
        Assert::assertEquals($expected, $actual, $msg);
    }

    /**
     * @param Grammar $expected
     * @param Grammar $actual
     */
    public function assertGrammarEquals(Grammar $expected, Grammar $actual)
    {
        foreach ($expected as $name => $expr) {
            Assert::assertArrayHasKey($name, $actual);
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
