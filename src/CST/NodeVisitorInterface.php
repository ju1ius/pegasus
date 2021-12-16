<?php declare(strict_types=1);

namespace ju1ius\Pegasus\CST;

/**
 * Generic Visitor interface
 */
interface NodeVisitorInterface
{
    /**
     * Called once before traversal.
     */
    public function beforeTraverse(Node $node): ?Node;

    /**
     * Called when entering a node.
     */
    public function enterNode(Node $node, ?int $index = null, bool $isLast = false): ?Node;

    /**
     * Called when leaving a node.
     */
    public function leaveNode(Node $node, ?int $index = null, bool $isLast = false): ?Node;

    /**
     * Called once after traversal.
     */
    public function afterTraverse(Node $node): ?Node;
}
