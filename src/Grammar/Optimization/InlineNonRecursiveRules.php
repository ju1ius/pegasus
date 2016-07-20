<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * References to non-recursive rules can be inlined without affecting how they parse,
 * assuming the referenced rule does not change.
 *
 * This optimization is only applied if the referenced rule is non-recursive and explicitly marked for inlining.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class InlineNonRecursiveRules extends Optimization
{
    /**
     * @inheritDoc
     */
    public function willPreProcessExpression(Expression $expr, OptimizationContext $context)
    {
        return $expr instanceof Reference && $context->isInlineableRule($expr->getIdentifier());
    }

    /**
     * @inheritDoc
     */
    public function preProcessExpression(Expression $expr, OptimizationContext $context)
    {
        /** @var Reference $expr */
        $referenced = $context->getRule($expr->getIdentifier());
        $cloned = clone $referenced;
        // In case of reference at the top-level of a rule
        $cloned->setName($expr->getName());

        return $cloned;
    }
}
