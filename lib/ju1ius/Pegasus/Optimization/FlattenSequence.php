<?php

namespace ju1ius\Pegasus\Optimization;


class FlattenSequence extends OptimizationSequence
{
    public function __construct(FlattenMatchingSequence $first, FlattenCapturingSequence $last)
    {
        parent::__construct($first, $last);
    }
}
