<?php

namespace ju1ius\Pegasus\Visitor;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;


class ExpressionReferencer extends ExpressionVisitor
{
	public function __construct(Grammar $grammar)
	{
		$this->grammar = $grammar;
	}

    public function beforeTraverse(Expression $expr)
    {
        // A top-level rule cannot be a reference to itself !
        // We use this because if the visitor is used on every rule of a grammar,
        // the first traversed rule will always return a refernce to itself, 
        // and the tree is never visited, unless we create references in
        // leaveNode, but this is unsafe for recursive rules.
        $this->isTopLevel = true;
    }
	
    public function enterExpression(Expression $expr)
	{
        if (!$this->isTopLevel && $expr->name && isset($this->grammar[$expr->name])) {
            return new Expression\Reference($expr->name);
        }
        $this->isTopLevel = false;
	}
}
