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
use ju1ius\Pegasus\Expression\Composite;
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

    public function leaveExpression(Expression $expr, $index = null, $isLast = false)
    {
        if (!$expr instanceof Reference) {
            return null;
        }
        $referencedRule = $expr->getIdentifier();
        $referenceChain = [$referencedRule];

        $expr = $this->grammar[$referencedRule];
        // if referenced expr is itself a reference to some other rule
        while ($expr instanceof Reference) {
            $referencedRule = $expr->getIdentifier();
            // expr references itself directly or indirectly
            if (in_array($referencedRule, $referenceChain)) {
                throw new CircularReference($expr, $referenceChain);
            }

            $referenceChain[] = $referencedRule;
            $expr = $this->grammar[$referencedRule];
        }

        return $expr;
    }
}
