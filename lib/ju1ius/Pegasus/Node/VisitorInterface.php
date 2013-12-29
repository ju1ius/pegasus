<?php

namespace ju1ius\Pegasus\Node;


interface VisitorInterface
{
    /**
     * Called when entering a branch node.
     */
    public function enter(Composite $node);

    /**
     * Called when leaving a branch node.
     */
    public function leave(Composite $node);

    /**
     * Called when visiting a leaf node.
     */
    public function visit(Leaf $node);
}
