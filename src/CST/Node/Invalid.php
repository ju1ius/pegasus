<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\CST\Node;

use ju1ius\Pegasus\CST\Node;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class Invalid extends Node
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
