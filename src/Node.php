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
     * The expression (or it's string representation when used in a generated parser)
     * that generated this node.
     *
     * @var string
     */
    public $expr;

    /**
     * The full text fed to the parser.
     *
     * @var string
     */
    public $full_text;

    /**
     * The position in the text where the expression started matching.
     *
     * @var string
     */
    public $start;

    /**
     * The position after start where the expression first didn't match.
     *
     * @var string
     */
    public $end;

    /**
     * @param string|Expression $expr       The expression (or it's string representation when used in a generated parser)
     *                                      that generated this node.
     * @param string            $full_text  The full text fed to the parser
     * @param int               $start      The position in the text where that expr started matching
     * @param int               $end        The position after start where the expr first didn't match.
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
            && $this->expr === $other->expr
            && $this->start === $other->start
            && $this->end === $other->end
            && $this->full_text === $other->full_text
        ;
    }

    public function notEquals($other=null)
    {
        return !$this->equals($other);
    }

    public function inspect($error=null)
    {
        $rule = $this->expr instanceof Expression
            ? $this->expr->name ?: $this->expr->asRhs()
            : (string) $this->expr
        ;
        return sprintf(
            '+ %s, Rule=> %s Match=> "%s" %s',
            str_replace('ju1ius\Pegasus\\', '', get_class($this)),
            $rule,
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
