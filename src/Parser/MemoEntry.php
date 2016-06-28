<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Parser;


class MemoEntry
{
    public $result;
    public $end;

    public function __construct($result, $end_pos)
    {
        $this->result = $result;
        $this->end = $end_pos;
    }
}
