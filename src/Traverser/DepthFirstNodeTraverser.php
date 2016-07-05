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

use ju1ius\Pegasus\Exception\VisitationError;
use ju1ius\Pegasus\Node;

/**
 * Performs a depth-first traversal of a parse tree.
 */
class DepthFirstNodeTraverser
{
    /**
     * @var array
     */
    protected $visitors = [];

    /**
     * @var array
     */
    protected $actions = [];

    /**
     * @var array
     */
    protected $ignored = [];

    public function __construct(array $config = [])
    {
        $this->actions = $this->getActions($config);
        $this->visitors = $this->getVisitors();
    }

    final public function traverse(Node $node)
    {
        $this->beforeTraverse($node);
        $node = $this->visit($node);
        if (null !== $result = $this->afterTraverse($node)) {
            $node = $result;
        }

        return $node;
    }

    /**
     * @param Node $node
     */
    protected function beforeTraverse(Node $node)
    {
    }

    /**
     * @param mixed $node
     *
     * @return mixed
     */
    protected function afterTraverse($node)
    {
        return null;
    }

    public function leaves($node, $children)
    {
        if (!$children) {
            return null;
        }
        foreach ($children as $child) {
            if (!$child instanceof Node\Composite) {
                yield $child;
            } else {
                foreach ($this->leaves($child, $child->children) as $leaf) {
                    yield $leaf;
                }
            }
        }
    }

    /**
     * Default visitor method.
     *
     * Returns the node verbatim, so it maintains the parse tree's structure.
     * Non-generic visitor methods can then use or ignore this at their discretion.
     * This works out well regardless of whether a subclass is trying
     * to make another tree, a flat string, or whatever.
     *
     * @param Node  $node            The node we're visiting
     * @param array $visitedChildren The results of visiting the children of that node
     *
     *
     * @return \ju1ius\Pegasus\Node
     */
    protected function genericVisit(Node $node, array $visitedChildren)
    {
        return $node;
    }

    /**
     *  Lift the first child of node up to replace the node.
     */
    protected function liftChild($node, $children)
    {
        return $children[0];
    }

    /**
     *  Lift the matched text of the node up to replace the node.
     */
    protected function liftValue($node, $children)
    {
        return (string)$node;
    }

    protected function liftChildren($node, $children)
    {
        return $children;
    }

    protected function join($node, $children)
    {
        return implode('', $children);
    }

    protected function toString($node, $children)
    {
        return (string)$node;
    }

    protected function toInt($node, $children)
    {
        return (int)(string)$node;
    }

    protected function toFloat($node, $children)
    {
        return (float)(string)$node;
    }

    /**
     * @TODO: If we need to optimize this, we can go back to putting subclasses
     * in charge of visiting children; they know when not to bother.
     * Or we can mark nodes as not descend-worthy in the grammar.
     *
     * @param Node $node
     *
     * @return mixed
     */
    private function visit(Node $node)
    {
        // ignored rule
        if (!$node) {
            return null;
        }
        $label = $node->name;
        if (isset($this->ignored[$label])) {
            return null;
        }

        try {
            // visit children before visiting node (depth first).
            $children = [];
            foreach ($node->children as $child) {
                // filter ignored (null) nodes
                if (null !== $result = $this->visit($child)) {
                    $children[] = $result;
                }
            }

            if (isset($this->visitors[$label])) {
                $visitor = $this->visitors[$label];

                return $visitor($node, ...$children);
            }

            return $this->genericVisit($node, $children);
        } catch (VisitationError $err) {
            throw $err;
        } catch (\Exception $err) {
            throw new VisitationError($node, $err->getMessage(), $err);
        }
    }

    /**
     * Returns a map from rule names to visitation methods
     *
     * @return array
     */
    private function getVisitors()
    {
        $refClass = new \ReflectionClass($this);
        $methods = [];
        foreach ($refClass->getMethods() as $refMethod) {
            $name = $refMethod->name;
            if (strpos($name, 'visit_') === 0) {
                $methods[substr($name, 6)] = $refMethod->getClosure($this);
            }
        }

        return $methods;
    }

    /**
     * Returns a map from rule names to actions
     *
     * @param array $config
     *
     * @return array
     */
    private function getActions(array $config)
    {
        if (isset($config['ignore'])) {
            $this->ignored = array_combine(
                $config['ignore'],
                array_fill(0, count($config['ignore']), true)
            );
        }
        $result = [];
        if (!isset($config['actions'])) {
            return $result;
        }
        foreach ($config['actions'] as $nodeName => $actions) {
            // actions are chainable, so make it an array
            if (!is_array($actions)) {
                $actions = [$actions];
            }
            foreach ($actions as $action) {
                if ($action instanceof \Closure) {
                    $result[$nodeName][] = $action->bindTo($this, $this);
                } elseif (method_exists($this, $action)) {
                    $result[$nodeName][] = [$this, $action];
                }
            }
        }

        return $result;
    }
}
