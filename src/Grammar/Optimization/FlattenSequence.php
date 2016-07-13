<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar\Optimization;

use ju1ius\Pegasus\Grammar\Optimization\Flattening\FlattenCapturingSequence;
use ju1ius\Pegasus\Grammar\Optimization\Flattening\FlattenMatchingSequence;
use ju1ius\Pegasus\Grammar\OptimizationSequence;

final class FlattenSequence extends OptimizationSequence
{
    public function __construct()
    {
        parent::__construct(new FlattenMatchingSequence(), new FlattenCapturingSequence());
    }
}
