<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Traverser;

use ju1ius\Pegasus\Visitor\VisitorInterface;

/**
 * Generic traverser interface
 */
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
     * Traverses a node using the registered visitors.
     *
     * @param mixed $node The node to traverse.
     *
     * @return mixed The result of the traversal.
     */
    public function traverse($node);
}
