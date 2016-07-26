<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\CST\Node;

use ju1ius\Pegasus\CST\Node;

/**
 * A Node that has child nodes.
 */
class Composite extends Node
{
    /**
     * @param string $name
     * @param int    $start
     * @param int    $end
     * @param array  $children
     */
    public function __construct($name, $start, $end, array $children)
    {
        $this->name = $name;
        $this->start = $start;
        $this->end = $end;
        $this->children = $children;
    }
}
