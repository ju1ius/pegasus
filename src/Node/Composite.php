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
 * A Node that has child nodes.
 */
class Composite extends Node
{
    /**
     * @param string $name
     * @param int    $start
     * @param int    $end
     * @param array  $children
     * @param array  $attributes
     */
    public function __construct($name, $start, $end, array $children, array $attributes = [])
    {
        parent::__construct($name, $start, $end, null, $children, $attributes);
    }
}
