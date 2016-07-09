<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Node;

use ju1ius\Pegasus\Node;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class Quantifier extends Composite
{
    public $isQuantifier = true;

    /**
     * @inheritDoc
     */
    public function __construct($name, $start, $end, $children, $optional = false)
    {
        parent::__construct($name, $start, $end, $children);
        $this->isOptional = $optional;
    }
}
