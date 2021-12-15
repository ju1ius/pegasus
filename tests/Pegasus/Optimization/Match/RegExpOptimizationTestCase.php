<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Optimization\Match;

use ju1ius\Pegasus\Grammar\Optimization\PCREManipulator;
use ju1ius\Pegasus\Tests\Optimization\OptimizationTestCase;

class RegExpOptimizationTestCase extends OptimizationTestCase
{
    protected function createOptimization(string $class)
    {
        $manipulator = new PCREManipulator('/');

        return new $class($manipulator);
    }
}
