<?php

namespace ju1ius\Pegasus\Visitor;

use ju1ius\Pegasus\AbstractGrammar;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Exception\GrammarException;


class ReferenceResolver extends ExpressionVisitor
{
    public function __construct(AbstractGrammar $grammar)
    {
        $this->grammar = $grammar;
    }

    public function enterNode(Expression $expr)
    {
        if ($expr instanceof Reference) {
            $label = $expr->identifier;
			if (!isset($this->grammar[$label])) {
				throw new GrammarException(sprintf(
					'Referenced rule "%s" is not defined in the grammar.',
					$label
				));
			}
			$reffed_expr = $this->grammar[$label];
			return $reffed_expr;
        }
    }
}
