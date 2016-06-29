<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Node;

use ju1ius\Pegasus\Node;


/**
 * A Node that has child nodes.
 */
class Composite extends Node
{
    /**
     * @var array List of child parse tree nodes.
     */
    public $children =  [];

    /**
     * @param string $expr      The name of the expression that generated me
     * @param string $full_text The full text fed to the parser
     * @param int    $start     The position in the text where that expr started matching
     * @param int    $end       The position after start where the expr first didn't match.
     * @param array  $children  List of child parse tree nodes
     */
    public function __construct($expr, $full_text, $start, $end, array $children)
    {
        parent::__construct($expr, $full_text, $start, $end);
        $this->children = $children;
    }
    
    public function equals($other=null)
    {
        return parent::equals($other)
            && $this->children === $other->children
        ;
    }

    public function inspect($error=null)
    {
        $ret = [parent::inspect($error)];
        foreach($this->children as $child) {
            $ret[] = self::indent($child->inspect($error));
        }
        return implode("\n", $ret);
    }

    public function toArray()
    {
        $res = [];
        foreach ($this->children as $child) {
            $res[] = $child instanceof Composite
                ? $child->toArray()
                : $child->getText()
            ;
        }
        return $res;
    }
    
    public function terminals()
    {
        foreach ($this->children as $child) {
            foreach ($child->terminals() as $terminal) {
                yield $terminal;
            }
        }
    }

    public function iter()
    {
        yield $this;
        foreach ($this->children as $child) {
            foreach ($child->iter() as $node) {
                yield $node;
            }
        }
    }
}
