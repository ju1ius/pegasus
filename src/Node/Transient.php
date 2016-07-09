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
 * A transient node is meant to be removed from the parse tree.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class Transient extends Node
{
    /**
     * @inheritDoc
     */
    public $isTransient = true;

    /**
     * @inheritDoc
     */
    public function __construct($start, $end)
    {
        parent::__construct('', $start, $end);
    }
}
