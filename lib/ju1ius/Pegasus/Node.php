<?php

namespace ju1ius\Pegasus;


/**
 * Abstract class for parse tree nodes.
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
abstract class Node
{
    /**
     * @var string The expression that generated this node.
     */
    public $expr;

    /**
     * @var string The full text fed to the parser.
     */
    public $full_text;

    /**
     * @var string The position in the text where the expression started matching.
     */
    public $start;

    /**
     * @var string The position after start where the expression first didn't match.
     */
    public $end;

    /**
     * @param string $expr_name The name of the expression that generated this node.
     * @param string $full_text The full text fed to the parser
     * @param int    $start     The position in the text where that expr started matching
     * @param int    $end       The position after start where the expr first didn't match.
     **/
    public function __construct($expr, $full_text, $start, $end)
    {
        $this->expr = $expr;
        $this->full_text = $full_text;
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Generator recursively yielding all terminal (leaf) nodes
     */
    abstract public function terminals();
    /**
     * Generator recursively yielding this node and it's children
     */
    abstract public function iter();

    public function __toString()
    {
        return $this->getText();
    }

    /**
     * Returns the text this node matched
     *
     * @return string
     */
    public function getText()
    {
        return (string) substr($this->full_text, $this->start, $this->end - $this->start);
    }

    public function equals($other=null)
    {
        return $other
            && $this instanceof $other
            && $this->expr->id === $other->expr->id
            && $this->start === $other->start
            && $this->end === $other->end
            && $this->full_text === $other->full_text
        ;
    }

    public function notEquals($other=null)
    {
        return !$this->equals($other);
    }

    public function treeview($error=null)
    {
        return sprintf(
            '+ %s, Rule=> %s Match=> "%s" %s',
            str_replace('ju1ius\Pegasus\\', '', get_class($this)),
            $this->expr->name ?: $this->expr->asRhs(),
            $this->getText(),
            $error === $this ? '    <-- *** We were here. ***' : ''
        );
    }
    
    static protected function indent($text)
    {
        return implode("\n", array_map(function($line) {
            return '+---' . $line;
        }, explode("\n", $text)));
    }
}
