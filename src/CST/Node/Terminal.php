<?php declare(strict_types=1);
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

class Terminal extends Node
{
    public $isTerminal = true;

    public function __construct(string $name, int $start, int $end, $value = null, array $attributes = [])
    {
        $this->name = $name;
        $this->start = $start;
        $this->end = $end;
        $this->value = $value;
        $this->attributes = $attributes;
    }
}
