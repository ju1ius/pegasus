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

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Exception\GrammarException;


class ExpressionDereferencer extends ExpressionVisitor
{
    public function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;
    }

    public function leaveExpression(Expression $expr)
    {
        if (!$expr instanceof Expression\Reference) {
            return null;
        }
        $label = $expr->identifier;
        $ref_stack = [$label];

        if (!isset($this->grammar[$label])) {
            throw new GrammarException(sprintf(
                'Referenced rule "%s" is not defined in the grammar.',
                $label
            ));
        }

        $expr = $this->grammar[$label];
        // if reffed_expr is itself a reference to some other rule
        while ($expr instanceof Expression\Reference) {

            // expr references itself directly or indirectly
            if (in_array($expr->identifier, $ref_stack)) {
                throw new GrammarException(
                    'Found circular reference "%s" (Reference chain: %s).',
                    $expr->identifier,
                    implode(' => ', $ref_stack)
                );
            }

            if (!isset($this->grammar[$expr->identifier])) {
                throw new GrammarException(sprintf(
                    'Referenced rule "%s" is not defined in the grammar (Reference chain: %s).',
                    $expr->identifier,
                    implode(' => ', $ref_stack)
                ));
            }

            $ref_stack[] = $expr->identifier;
            $expr = $this->grammar[$expr->identifier];
        }

        return $expr;
    }
}
