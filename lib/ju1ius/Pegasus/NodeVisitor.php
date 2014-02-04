<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Exception\VisitationError;
use ju1ius\Pegasus\Node;


/**
 * A shell for writing things that turn parse trees into something useful
 *
 * Performs a depth-first traversal of an AST.
 * Subclass this, add methods for each expr you care about,
 * instantiate, and call ->visit(top_node_of_parse_tree).
 * It'll return the useful stuff.
 *
 * This API is very similar to that of ``ast.NodeVisitor``.
 *
 * We never transform the parse tree in place, because...
 *
 * - There are likely multiple references to the same ``Node`` object in a
 * parse tree, and changes to one reference would surprise you elsewhere.
 * - It makes it impossible to report errors: you'd end up with the "error"
 * arrow pointing someplace in a half-transformed mishmash of nodes--and
 * that's assuming you're even transforming the tree into another tree.
 * Heaven forbid you're making it into a string or something else.
 **/
class NodeVisitor
{
    protected $visitors = [];
    protected $actions = [];
    protected $ignored = [];

    public function __construct(array $config=[])
    {
        $this->actions = $this->getActions($config);
        $this->visitors = $this->getVisitors();
    }

    /**
     * @TODO: If we need to optimize this, we can go back to putting subclasses
     * in charge of visiting children; they know when not to bother.
     * Or we can mark nodes as not descend-worthy in the grammar.
     **/
    public function visit($node)
    {
        try {
            // ignored rule
            if (!$node) return;
            $label = is_string($node->expr)
                ? $node->expr
                : $node instanceof Node\Label
                    ? $node->expr->label
                    : $node->expr->name
            ;
            if (isset($this->ignored[$label])) return;

            // visit children before visiting node.
            $children = [];
            if ($node instanceof Node\Composite) {
                // visit children
				foreach ($node->children as $child) {
					// filter ignored (null) nodes 
                    if (null !== $result = $this->visit($child)) {
                        $children[] = $result;
                    }
				}
            }

            //if (isset($this->actions[$node->expr_name])) {
                //$actions = $this->actions[$node->expr_name];
                //$res = $node;
                //foreach ($actions as $action) {
                    //$res = call_user_func($action, $res, $children);
                //}
                //return $res;
            //}

            $visitor = isset($this->visitors[$label])
                ? $this->visitors[$label]
                : 'generic_visit'
            ;

            return $this->$visitor($node, $children);

        } catch (VisitationError $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new VisitationError($e, $node);
        }
    }

    /**
     * Default visitor method
     *
     * Return the node verbatim, so it maintains the parse tree's structure.
     * Non-generic visitor methods can then use or ignore this at their discretion.
     * This works out well regardless of whether a subclass is trying
     * to make another tree, a flat string, or whatever.
     *
     * @param Node  $node             The node we're visiting
     * @param array $visited_children The results of visiting the children of that node
     *
     **/
    public function generic_visit(Node $node, array $visited_children)
    {
        return $node;
    }

    public function leaves($node, $children)
    {
        if (!$children) return;
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
     *  Lift the sole child of node up to replace the node.
     */
    public function liftChild($node, $children)
    {
        return $children[0];
    }

    /**
     *  Lift the matched text of the node up to replace the node.
     */
    public function liftValue($node, $children)
    {
        return (string) $node;
    }

    public function liftChildren($node, $children)
    {
        return $children;
    }

    public function join($node, $children)
    {
        return implode('', $children);
    }

    public function toString($node, $children)
    {
        return (string) $node;
    }

    public function toInt($node, $children)
    {
        return (int) (string) $node;
    }
    
    public function toFloat($node, $children)
    {
        return (float) (string) $node;
    }

    /**
     * Returns a map from rule names to visitation methods
     */
    private function getVisitors()
    {
        $refclass = new \ReflectionClass($this);
        $methods = [];
        foreach ($refclass->getMethods() as $refmethod) {
            $name = $refmethod->name;
            if (0 === strpos($name, 'visit_')) {
                $methods[substr($name, 6)] = $name;
            }
        }
        return $methods;
    }

    /**
     * Returns a map from rule names to actions
     */
    private function getActions(array $config)
    {
        if (isset($config['ignore'])) {
            $this->ignored = array_combine($config['ignore'], array_fill(0, count($config['ignore']), true));
        }
        $result = [];
        if (!isset($config['actions'])) return $result;
        foreach ($config['actions'] as $expr_name => $actions) {
            // actions are chainable, so make it an array
            if (!is_array($actions)) {
                $actions = [$actions];
            }
            foreach ($actions as $action) {
                if ($action instanceof \Closure) {
                    $result[$expr_name][] = $action->bindTo($this, $this);
                } else if (method_exists($this, $action)) {
                    $result[$expr_name][] = [$this, $action];
                }
            }
        }
        return $result;
    }
}
