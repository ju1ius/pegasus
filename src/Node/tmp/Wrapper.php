<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Node;

use ju1ius\Pegasus\Node;

/**
 * A composite node having only one child node.
 */
class Wrapper extends Composite
{
    public function __construct($expr_name, $full_text, $start, $end, $child)
    {
        if (!is_array($child)) {
            $child = [$child];
        } elseif (count($child) > 1) {
            throw new \InvalidArgumentException('Wrapper nodes can have only one child.');
        }
        parent::__construct($expr_name, $full_text, $start, $end, $child);
    }
}
