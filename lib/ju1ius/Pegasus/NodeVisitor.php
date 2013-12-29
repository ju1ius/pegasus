<?php

namespace ju1ius\Pegasus;

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

    public function __construct(array $actions=[])
    {
        $this->actions = $this->getActions($actions);
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
            if (!$node || isset($this->ignored[$node->expr_name])) return null;

            // filter dropped (null) nodes before visiting
            $children = array_map([$this, 'visit'], $node->children);
            $children = array_values(array_filter($children, function($child) {
                return $child !== null;
            }));

            if (isset($this->actions[$node->expr_name])) {
                $actions = $this->actions[$node->expr_name];
                foreach ($actions as $action) {
                    $node = call_user_func($action, $node, $children);
                }
                return $node;
            }

            $visitor = isset($this->visitors[$node->expr_name])
                ? $this->visitors[$node->expr_name]
                : 'generic_visit';

            return $this->$visitor($node, $children);

        } catch (VisitationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new VisitationException($node, $e);
        }
    }

    /**
     * Default visitor method
     *
     * Return the node verbatim, so it maintains the parse tree's structure.
     * Non-generic visitor methods can then use or ignore this at their
     * discretion. This works out well regardless of whether a subclass is
     * trying to make another tree, a flat string, or whatever.
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
            if (!$child instanceof Node) {
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
        return $children ?: $node;
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
        $result = [];
        foreach ($config as $expr_name => $actions) {
            // actions are chainable, so make it an array
            if (!is_array($actions)) {
                $actions = [$actions];
            }
            // ignore action override any others
            if (in_array('ignore', $actions)) {
                $this->ignored[$expr_name] = true;
                continue;
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
