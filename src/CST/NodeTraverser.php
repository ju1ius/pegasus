<?php declare(strict_types=1);

namespace ju1ius\Pegasus\CST;

use ju1ius\Pegasus\CST\Node\Composite;
use ju1ius\Pegasus\CST\Node\Invalid;

class NodeTraverser implements NodeTraverserInterface
{
    /**
     * @var \SplObjectStorage.<NodeVisitorInterface>
     */
    protected $visitors;

    public function __construct()
    {
        $this->visitors = new \SplObjectStorage();
    }

    /**
     * @inheritDoc
     */
    public function addVisitor(NodeVisitorInterface ...$visitors)
    {
        foreach ($visitors as $visitor) {
            $this->visitors->attach($visitor);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeVisitor(NodeVisitorInterface ...$visitors)
    {
        foreach ($visitors as $visitor) {
            $this->visitors->detach($visitor);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function traverse(Node $node)
    {
        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->beforeTraverse($node)) {
                $node = $result;
            }
        }

        if (null !== $result = $this->traverseNode($node)) {
            $node = $result;
        }

        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->afterTraverse($node)) {
                $node = $result;
            }
        }

        return $node;
    }

    protected function traverseNode(Node $node, ?int $index = null, bool $isLast = false)
    {
        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->enterNode($node, $index, $isLast)) {
                $node = $result;
            }
        }

        if ($node instanceof Composite) {
            $childCount = \count($node->children);
            foreach ($node->children as $i => $child) {
                if (!$child instanceof Node) {
                    $child = new Invalid($child);
                }
                if (null !== $result = $this->traverseNode($child, $i, $i === $childCount - 1)) {
                    $node->children[$i] = $result;
                }
            }
        }

        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->leaveNode($node, $index, $isLast)) {
                $node = $result;
            }
        }

        return $node;
    }
}
