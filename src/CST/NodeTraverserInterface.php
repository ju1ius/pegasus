<?php declare(strict_types=1);

namespace ju1ius\Pegasus\CST;

/**
 * Generic traverser interface
 */
interface NodeTraverserInterface
{
    /**
     * Adds a visitor.
     * @return $this
     */
    public function addVisitor(NodeVisitorInterface ...$visitors): static;

    /**
     * Removes an added visitor.
     * @return $this
     */
    public function removeVisitor(NodeVisitorInterface ...$visitors): static;

    /**
     * Traverses a node using the registered optimizations.
     */
    public function traverse(Node $node): ?Node;
}
