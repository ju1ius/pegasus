<?php declare(strict_types=1);

namespace ju1ius\Pegasus\CST;

abstract class NodeVisitor implements NodeVisitorInterface
{
    public function beforeTraverse(Node $node): ?Node
    {
        return null;
    }

    public function afterTraverse(Node $node): ?Node
    {
        return null;
    }

    public function enterNode(Node $node, ?int $index = null, bool $isLast = false): ?Node
    {
        return null;
    }

    public function leaveNode(Node $node, ?int $index = null, bool $isLast = false): ?Node
    {
        return null;
    }
}
