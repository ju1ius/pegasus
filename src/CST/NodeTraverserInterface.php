<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\CST;

/**
 * Generic traverser interface
 */
interface NodeTraverserInterface
{
    /**
     * Adds a visitor.
     *
     * @param \ju1ius\Pegasus\CST\NodeVisitorInterface[] ...$visitors
     *
     * @return $this
     */
    public function addVisitor(NodeVisitorInterface ...$visitors);

    /**
     * Removes an added visitor.
     *
     * @param \ju1ius\Pegasus\CST\NodeVisitorInterface[] ...$visitors
     *
     * @return $this
     */
    public function removeVisitor(NodeVisitorInterface ...$visitors);

    /**
     * Traverses a node using the registered optimizations.
     *
     * @param mixed $node The node to traverse.
     *
     * @return mixed The result of the traversal.
     */
    public function traverse(Node $node);
}
