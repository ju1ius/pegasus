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

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Exception\CircularReference;
use ju1ius\Pegasus\Grammar\Exception\GrammarException;
use ju1ius\Pegasus\Grammar\Exception\RuleNotFound;

/**
 * Folds expressions by resolving references identifiers to Expression instances.
 */
class ExpressionFolder extends ExpressionVisitor
{
    /**
     * @var Grammar
     */
    private $grammar;

    public function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;
    }

    public function leaveExpression(Expression $expr)
    {
        if (!$expr instanceof Reference) {
            return null;
        }
        $label = $expr->identifier;
        $referenceChain = [$label];

        if (!isset($this->grammar[$label])) {
            throw new RuleNotFound($label);
        }

        $expr = $this->grammar[$label];
        // if reffed_expr is itself a reference to some other rule
        while ($expr instanceof Reference) {

            // expr references itself directly or indirectly
            if (in_array($expr->identifier, $referenceChain)) {
                throw new CircularReference($expr, $referenceChain);
            }

            if (!isset($this->grammar[$expr->identifier])) {
                throw new GrammarException(sprintf(
                    'Referenced rule "%s" is not defined in the grammar (Reference chain: %s).',
                    $expr->identifier,
                    implode(' => ', $referenceChain)
                ));
            }

            $referenceChain[] = $expr->identifier;
            $expr = $this->grammar[$expr->identifier];
        }

        return $expr;
    }
}
