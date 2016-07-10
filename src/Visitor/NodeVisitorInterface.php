<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Visitor;

use ju1ius\Pegasus\Node;

/**
 * Generic Visitor interface
 */
interface NodeVisitorInterface
{
    /**
     * Called once before traversal.
     *
     * @param Node $node
     *
     * @return Node
     */
    public function beforeTraverse(Node $node);

    /**
     * Called when entering a node.
     *
     * @param Node     $node
     * @param Node     $parent
     * @param int|null $index
     *
     * @return Node
     */
    public function enterNode(Node $node, Node $parent = null, $index = null);

    /**
     * Called when leaving a node.
     *
     * @param Node     $node
     * @param Node     $parent
     * @param int|null $index
     *
     * @return Node
     */
    public function leaveNode(Node $node, Node $parent = null, $index = null);

    /**
     * Called once after traversal.
     *
     * @param Node $node
     *
     * @return Node
     */
    public function afterTraverse(Node $node);
}
