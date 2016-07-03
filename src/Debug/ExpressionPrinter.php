<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Debug;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Visitor\ExpressionVisitor;

final class ExpressionPrinter extends ExpressionVisitor
{
    /**
     * @var int
     */
    private $depth = 0;

    public function beforeTraverse(Expression $expr)
    {
        $this->depth = 0;
    }

    public function enterExpression(Expression $expr)
    {
        if ($expr instanceof Composite) {
            $this->depth++;
        }
        $indent = str_repeat('│ ', $this->depth - 1);
        $indent .= $expr instanceof Composite ? '├ ' : '└ ';
        echo sprintf(
            "%s<%s: %s>\n",
            $indent,
            str_replace('ju1ius\Pegasus\Expression\\', '', get_class($expr)),
            $expr->asRule()
        );
    }

    public function leaveExpression(Expression $expr)
    {
        if ($expr instanceof Composite) {
            $this->depth--;
        }
    }
}
