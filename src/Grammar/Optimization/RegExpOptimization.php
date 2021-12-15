<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization;

use ju1ius\Pegasus\Grammar\Optimization;

abstract class RegExpOptimization extends Optimization
{
    public function __construct(
        protected RegExpManipulator $manipulator
    ) {
    }
}
