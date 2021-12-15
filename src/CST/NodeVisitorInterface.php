<?php declare(strict_types=1);

namespace ju1ius\Pegasus\CST;

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
     * @param Node $node
     * @param int|null $index
     * @param bool $isLast
     *
     * @return Node
     */
    public function enterNode(Node $node, ?int $index = null, bool $isLast = false);

    /**
     * Called when leaving a node.
     *
     * @param Node $node
     * @param int|null $index
     * @param bool $isLast
     *
     * @return Node
     */
    public function leaveNode(Node $node, ?int $index = null, bool $isLast = false);

    /**
     * Called once after traversal.
     *
     * @param Node $node
     *
     * @return Node
     */
    public function afterTraverse(Node $node);
}
