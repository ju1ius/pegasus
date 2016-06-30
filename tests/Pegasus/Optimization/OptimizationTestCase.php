<?php

namespace ju1ius\Pegasus\Tests\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Optimization\Optimization;
use ju1ius\Pegasus\Tests\PegasusTestCase;

class OptimizationTestCase extends PegasusTestCase
{
    protected function applyOptimization(Optimization $optim, Expression $expr)
    {
        if ($expr instanceof Composite) {
            foreach ($expr as $i => $child) {
                $expr[$i] = $this->applyOptimization($optim, $child);
            }
        }

        return $optim->apply($expr);
    }
}
