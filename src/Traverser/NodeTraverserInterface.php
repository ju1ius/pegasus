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

use ju1ius\Pegasus\Visitor\NodeVisitorInterface;

/**
 * Generic traverser interface
 */
interface NodeTraverserInterface
{
    /**
     * Adds a visitor.
     *
     * @param NodeVisitorInterface[] ...$visitors
     *
     * @return $this
     */
    public function addVisitor(NodeVisitorInterface ...$visitors);

    /**
     * Removes an added visitor.
     *
     * @param NodeVisitorInterface[] ...$visitors
     *
     * @return $this
     */
    public function removeVisitor(NodeVisitorInterface ...$visitors);

    /**
     * Traverses a node using the registered visitors.
     *
     * @param mixed $node The node to traverse.
     *
     * @return mixed The result of the traversal.
     */
    public function traverse($node);
}
