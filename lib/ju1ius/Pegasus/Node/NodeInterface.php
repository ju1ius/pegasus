<?php

namespace ju1ius\Pegasus\Node;


interface NodeInterface
{
    public function accept(NodeVisitorInterface $visitor);
}
