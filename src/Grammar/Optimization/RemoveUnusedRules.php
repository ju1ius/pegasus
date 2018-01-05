<?php declare(strict_types=1);
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
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * Removes rules that are not referenced from other rules.
 *
 * This should be done in a separate OptimizationPass, at the end of the optimization process.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class RemoveUnusedRules extends Optimization
{
    /**
     * @inheritDoc
     */
    public function afterTraverse(Grammar $grammar, OptimizationContext $context): ?Grammar
    {
        $references = array_flip($context->getReferencedRules());

        foreach ($grammar as $name => $expr) {
            if (!isset($references[$name])) {
                unset($grammar[$name]);
            }
        }

        return $grammar;
    }
}
