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
    public function terminals()
    {
        yield $this;
    }
    public function iter()
    {
        yield $this;
    }
}
