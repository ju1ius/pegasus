<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Grammar\Optimization;


use ju1ius\Pegasus\Grammar\Optimization;


abstract class RegExpOptimization extends Optimization
{
    /**
     * @var RegExpManipulator
     */
    protected $manipulator;

    public function __construct(RegExpManipulator $manipulator)
    {
        $this->manipulator = $manipulator;
    }
}
