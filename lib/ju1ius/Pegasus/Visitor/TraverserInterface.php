<?php

namespace ju1ius\Pegasus\Visitor;


interface TraverserInterface
{
    /**
     * Adds a visitor.
     *
     * @param VisitorInterface $visitor Visitor to add
     */
    public function addVisitor(VisitorInterface $visitor);

    /**
     * Removes an added visitor.
     *
     * @param VisitorInterface $visitor
     */
    public function removeVisitor(VisitorInterface $visitor);

    /**
     * Traverses an array of nodes using the registered visitors.
     *
     * @param mixed $node The node to travers
     *
     * @return mixed The result of the traversal.
     */
    public function traverse($node);
}
