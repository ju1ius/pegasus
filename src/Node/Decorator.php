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
abstract class Decorator extends Composite
{
    /**
     * @inheritDoc
     */
    public function __construct($name, $start, $end, $fullText, array $children = [], array $attributes = [])
    {
        if (count($children) > 1) {
            throw new \LogicException('Decorator nodes can have only one child.');
        }
        parent::__construct($name, $start, $end, $fullText, $children, $attributes);
    }
}
