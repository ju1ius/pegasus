<?php

namespace ju1ius\Pegasus\Node;


class Visitor implements VisitorInterface
{
    public function enter(Composite $node)
    {
        return true;
    }
    public function leave(Composite $node)
    {
        return $node;
    }
    public function visit(Leaf $node)
    {
        return $node;
    }
}
