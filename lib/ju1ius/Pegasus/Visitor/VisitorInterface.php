<?php

namespace ju1ius\Pegasus\Visitor;


/**
 * Generic Visitor interface
 *
 */
interface VisitorInterface
{
    /**
     * Called once before traversal.
     *
     */
    public function beforeTraverse($node);

    /**
     * Called when entering a node.
     *
     */
    public function enterNode($node);

    /**
     * Called when leaving a node.
     *
     */
    public function leaveNode($node);

    /**
     * Called once after traversal.
     *
     */
    public function afterTraverse($nodes);
}
