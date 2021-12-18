<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Terminal\GroupMatch;
use ju1ius\Pegasus\Grammar;
use PHPUnit\Framework\Assert;

final class PegasusAssert
{
    /**
     * Asserts that two expression are equals, omitting their id property.
     */
    public static function expressionEquals(Expression $expected, Expression $actual, string $msg = '')
    {
        self::cleanupExpr($expected);
        self::cleanupExpr($actual);
        Assert::assertEquals($expected, $actual, $msg);
    }

    public static function grammarEquals(Grammar $expected, Grammar $actual)
    {
        foreach ($expected as $name => $expr) {
            Assert::assertArrayHasKey($name, $actual);
            self::expressionEquals($expr, $actual[$name]);
        }
    }

    /**
     * Compares two nodes.
     */
    public static function nodeEquals(Node $expected, Node $actual, string $message = '')
    {
        Assert::assertEquals($expected, $actual, $message);
    }

    private static function cleanupExpr(Expression $expr)
    {
        $expr->id = -1;
        if ($expr instanceof Composite) {
            foreach ($expr as $child) {
                self::cleanupExpr($child);
            }
        } else if ($expr instanceof GroupMatch) {
            $expr->getMatcher()->id = -1;
        }
    }
}
