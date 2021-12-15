<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Exception\MissingStartRule;
use ju1ius\Pegasus\Grammar\Optimization;
use ju1ius\Pegasus\Grammar\OptimizationContext;

/**
 * Removes rules that are not referenced from other rules.
 * This should be done in a separate OptimizationPass, at the end of the optimization process.
 */
class RemoveUnusedRules extends Optimization
{
    /**
     * @throws MissingStartRule
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
