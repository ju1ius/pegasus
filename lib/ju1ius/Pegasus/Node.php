<?php

namespace ju1ius\Pegasus;

/**
 * A parse tree node
 *
 * Consider these immutable once constructed. As a side effect of a
 * memory-saving strategy in the cache, multiple references to a single
 * ``Node`` might be returned in a single parse tree. So, if you started
 * messing with one, you'd see surprising parallel changes pop up elsewhere.
 *
 * My philosophy is that parse trees (and their nodes) should be
 * representation-agnostic. That is, they shouldn't get all mixed up with what
 * the final rendered form of a wiki page (or the intermediate representation
 * of a programming language, or whatever) is going to be: you should be able
 * to parse once and render several representations from the tree, one after
 * another. 
 *
 **/
class Node
{
    public
        $expr_name,
        $full_text,
        $start,
        $end,
        $children;

    /**
     * @param string $expr_name The name of the expression that generated me
     * @param string $full_text The full text fed to the parser
     * @param int    $start     The position in the text where that expr started matching
     * @param int    $end       The position after start where the expr first didn't match.
     * @param array  $children  List of child parse tree nodes
     **/
    public function __construct($expr_name, $full_text, $start, $end, $children=[])
    {
        $this->expr_name = $expr_name;
        $this->full_text = $full_text;
        $this->start = $start;
        $this->end = $end;
        $this->children = $children;
    }

    public function __toString()
    {
        return $this->getText();
    }

    /**
     * Returns the text this node matched
     **/
    public function getText()
    {
        return substr($this->full_text, $this->start, $this->end - $this->start);
    }

    public function equals($other = null)
    {
        return $other !== null
            && $this->expr_name === $other->name
            && $this->full_text === $other->full_text
            && $this->start === $other->start
            && $this->end === $other->end
            && $this->children === $other->children
        ;
    }

    public function notEquals($other=null)
    {
        return !$this->equals($other);
    }

    /**
     * Generator yielding all leaf nodes
     */
    public function leaves()
    {
        if (!$this->children) {
            yield $this;
            return;
        }
        foreach ($this->children as $child) {
            foreach ($child->leaves() as $leaf) {
                yield $leaf;
            }
        }
    }

    public function treeView($error=null)
    {
        $ret = [sprintf(
            '<%s "%s" matching "%s">%s',
            get_class($this),
            $this->expr_name ?: '',
            $this->getText(),
            $error === $this ? '  <-- *** We were here. ***' : ''
        )];
        foreach($this->children as $child) {
            $ret[] = self::indent($child->treeView($error));
        }
        return implode("\n", $ret);
    }
    
    static protected function indent($text)
    {
        return implode("\n", array_map(function($line) {
            return '    ' . $line;
        }, explode("\n", $text)));
    }
}
