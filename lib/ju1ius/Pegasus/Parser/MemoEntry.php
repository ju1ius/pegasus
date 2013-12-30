<?php

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
