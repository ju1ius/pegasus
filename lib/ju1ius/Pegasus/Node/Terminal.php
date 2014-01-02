<?php

namespace ju1ius\Pegasus\Node;

use ju1ius\Pegasus\Node;


class Terminal extends Node
{
    public function terminals()
    {
        yield $this;
    }
}
