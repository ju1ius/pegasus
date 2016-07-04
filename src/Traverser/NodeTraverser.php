<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Traverser;

use ju1ius\Pegasus\Visitor\NodeVisitorInterface;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
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
    public function traverse($node)
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

    protected function traverseNode($node)
    {
        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->enterNode($node)) {
                $node = $result;
            }
        }

        foreach ($node->children as $i => $child) {
            if (null !== $result = $this->traverseNode($child)) {
                $node->children[$i] = $result;
            }
        }

        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->leaveNode($node)) {
                $node = $result;
            }
        }

        return $node;
    }
}
