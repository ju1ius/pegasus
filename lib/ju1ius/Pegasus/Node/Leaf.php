<?php

namespace ju1ius\Pegasus\Node;


class Leaf extends Node
{
    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visit($this);
    }    
}
