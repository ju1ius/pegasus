<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Visitor;

use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;

/**
 * Unfolds expressions by turning circular references to Reference expression instances.
 */
class ExpressionUnfolder extends ExpressionVisitor
{
    /**
     * @var bool
     */
    private $isTopLevel = true;

    /**
     * @var Grammar
     */
    private $grammar;

    public function __construct(Grammar $grammar)
	{
		$this->grammar = $grammar;
	}

    public function beforeTraverse(Expression $expr)
    {
        // A top-level rule cannot be a reference to itself !
        // We use this because if the visitor is used on every rule of a grammar,
        // the first traversed rule will always return a reference to itself,
        // and the tree is never visited, unless we create references in
        // leaveNode, but this is unsafe for recursive rules.
        $this->isTopLevel = true;
    }

    public function enterExpression(Expression $expr)
	{
        if (!$this->isTopLevel && $expr->name && isset($this->grammar[$expr->name])) {
            return new Reference($expr->name);
        }
        $this->isTopLevel = false;
	}
}
