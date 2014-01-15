<?php

namespace ju1ius\Test\Pegasus\Optimization;

use ju1ius\Test\Pegasus\PegasusTestCase;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Optimization\Optimization;


class OptimizationTestCase extends PegasusTestCase
{
    protected function apply(Optimization $optim, Expression $expr)
    {
        if ($expr instanceof Composite) {
            foreach ($expr->members as $i => $child) {
                $expr->members[$i] = $this->apply($optim, $child);
            }
        }
        return $optim->apply($expr);
    }
}
