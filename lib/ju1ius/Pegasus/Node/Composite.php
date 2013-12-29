<?php

namespace ju1ius\Pegasus\Node;


class Composite extends Node
{
    public $children;

    /**
     * @param string $expr_name The name of the expression that generated me
     * @param string $full_text The full text fed to the parser
     * @param int    $start     The position in the text where that expr started matching
     * @param int    $end       The position after start where the expr first didn't match.
     * @param array  $children  List of child parse tree nodes
     **/
    public function __construct($expr_name, $full_text, $start, $end, $children=[])
    {
        parent::__construct($expr_name, $full_text, $start, $end);
        $this->children = $children;
    }

    public function accept(VisitorInterface $visitor)
    {
        if ($visitor->enter($this)) {
            foreach ($this->children as $child) {
                if (!$child->accept($visitor)) break;   
            }
        }

        return $visitor->leave($this);
    }

    public function equals(Node $other=null)
    {
        return parent::equals($other)
            && $this->children === $other->children
        ;
    }

    /**
     * Generator yielding all leaf nodes
     */
    public function leaves()
    {
        foreach ($this->children as $child) {
            foreach ($child->leaves() as $leaf) {
                yield $leaf;
            }
        }
    }

    public function treeView($error=null)
    {
        $ret = [parent::treeView($error)];
        foreach($this->children as $child) {
            $ret[] = self::indent($child->treeView($error));
        }
        return implode("\n", $ret);
    }
    
}
