<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Optimization;


class FlattenSequence extends OptimizationSequence
{
    public function __construct(FlattenMatchingSequence $first, FlattenCapturingSequence $last)
    {
        parent::__construct($first, $last);
    }
}
