<?php

namespace ju1ius\Pegasus\Node;

use ju1ius\Pegasus\Node;


/**
 * A composite node having only one child node.
 */
class Wrapper extends Composite
{
    public function __construct($expr, $full_text, $start, $end, $child=[])
    {
        if (!is_array($child)) {
            $child = [$child];
        } elseif (count($child) > 1) {
            throw new \InvalidArgumentException('Wrapper nodes can have only one child.');
        }
        parent::__construct($expr, $full_text, $start, $end, $child);
    }
}
