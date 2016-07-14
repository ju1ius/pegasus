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

use ju1ius\Pegasus\Traverser\Exception\ParseTreeVisitationError;
use ju1ius\Pegasus\Node;

/**
 * Performs a depth-first traversal of a parse tree.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class NamedNodeTraverser
{
    /**
     * @var array
     */
    private $enterVisitors;

    /**
     * @var array
     */
    private $leaveVisitors;

    /**
     * @var Node
     */
    private $rootNode;

    final public function traverse(Node $node)
    {
        if ($this->leaveVisitors === null) {
            $this->buildVisitors();
        }

        gc_disable();

        $this->beforeTraverse($node);
        $node = $this->visit($node);
        $result = $this->afterTraverse($node);

        gc_enable();

        return $result;
    }

    /**
     * @param Node $node
     */
    protected function beforeTraverse(Node $node)
    {
        $this->rootNode = $node;
    }

    /**
     * @param mixed $node
     *
     * @return mixed
     */
    protected function afterTraverse($node)
    {
        $this->rootNode = null;

        return $node;
    }

    /**
     * @param Node  $node     The node we're visiting
     * @param array $children The results of visiting the children of that node
     *
     * @return mixed
     */
    protected function leaveNode(Node $node, array $children)
    {
        if ($node->isTransient) {
            // skip transient nodes (shouldn't happen)
            return null;
        }
        if ($node->isTerminal) {
            if (isset($node['matches'])) {
                return $node['matches'];
            }

            return $node->value;
        }
        if ($node->isQuantifier) {
            if ($node->isOptional) {
                return $children ? $children[0] : null;
            }

            return $children;
        }
        if ($node instanceof Node\Decorator) {
            return $children[0];
        }
        if (count($children) === 1) {
            return $children[0];
        }

        return $children;
    }

    /**
     * @param Node $node
     *
     * @return mixed
     */
    private function visit(Node $node)
    {
        $label = $node->name;

        try {
            if (isset($this->enterVisitors[$label])) {
                $this->enterVisitors[$label]($node);
            }

            $children = [];
            foreach ($node->children as $child) {
                $children[] = $this->visit($child);
            }

            if (isset($this->leaveVisitors[$label])) {
                return $this->leaveVisitors[$label]($node, ...$children);
            }

            return $this->leaveNode($node, $children);
        } catch (ParseTreeVisitationError $err) {
            throw $err;
        } catch (\Exception $err) {
            throw new ParseTreeVisitationError($node, $this->rootNode, '', $err);
        }
    }

    /**
     * Returns a map from rule names to visitation methods
     *
     * @return array
     */
    private function buildVisitors()
    {
        $this->enterVisitors = $this->leaveVisitors = [];
        $refClass = new \ReflectionClass($this);
        foreach ($refClass->getMethods() as $refMethod) {
            $name = $refMethod->name;
            if (strpos($name, 'leave_') === 0) {
                $this->leaveVisitors[substr($name, 6)] = $refMethod->getClosure($this);
            } elseif (strpos($name, 'enter_') === 0) {
                $this->enterVisitors[substr($name, 6)] = $refMethod->getClosure($this);
            }
        }
    }
}
