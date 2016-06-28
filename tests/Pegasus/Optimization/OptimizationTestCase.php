<?php

namespace ju1ius\Pegasus\Tests\Optimization;

use ju1ius\Test\Pegasus\PegasusTestCase;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Optimization\Optimization;


class OptimizationTestCase extends PegasusTestCase
{
    protected function apply(Optimization $optim, Expression $expr)
    {
        if ($expr instanceof Composite) {
            foreach ($expr->children as $i => $child) {
                $expr->children[$i] = $this->apply($optim, $child);
            }
        }
        return $optim->apply($expr);
    }
}
