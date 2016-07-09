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

class Terminal extends Node
{
    public $isTerminal = true;

    /**
     * @param string $name
     * @param int    $start
     * @param int    $end
     * @param null   $value
     * @param array  $attributes
     */
    public function __construct($name, $start, $end, $value = null, array $attributes = [])
    {
        parent::__construct($name, $start, $end, $value, [], $attributes);
    }
}
