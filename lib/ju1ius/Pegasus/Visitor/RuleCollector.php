<?php

namespace ju1ius\Pegasus\Visitor;

use ju1ius\Pegasus\AbstractGrammar;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Exception\GrammarException;


class RuleCollector extends ExpressionVisitor
{
    public function __construct(AbstractGrammar $grammar)
    {
        $this->grammar = $grammar;
    }

    public function leaveNode(Expression $expr)
    {
		if ($expr->name) {
			$this->grammar[$expr->name] = $expr;
		}
    }
}
