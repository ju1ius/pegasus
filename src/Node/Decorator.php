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
class Decorator extends Composite
{
    /**
     * @param string    $name
     * @param int       $start
     * @param int       $end
     * @param Node|null $child
     * @param array     $attributes
     */
    public function __construct($name, $start, $end, Node $child = null, array $attributes = [])
    {
        parent::__construct($name, $start, $end, $child ? [$child] : [], $attributes);
    }
}
