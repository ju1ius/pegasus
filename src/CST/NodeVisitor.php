<?php declare(strict_types=1);

namespace ju1ius\Pegasus\CST;

abstract class NodeVisitor implements NodeVisitorInterface
{
    /**
     * @inheritDoc
     */
    public function beforeTraverse(Node $node)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function afterTraverse(Node $node)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node, ?int $index = null, bool $isLast = false)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function leaveNode(Node $node, ?int $index = null, bool $isLast = false)
    {
        return null;
    }
}
