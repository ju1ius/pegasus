<?php

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Exception\IncompleteParseError;
use ju1ius\Pegasus\Exception\ParseError;
use ju1ius\Pegasus\Parser\ParserInterface;


/**
 * A thing that can be matched against a piece of text 
 **/
abstract class Expression
{
    public $name;
    public $id;

    public function __construct($name='')
    {
        $this->name = $name;
        $this->id = \spl_object_hash($this);
    }

    abstract public function asRhs();
    abstract public function match($text, $pos, ParserInterface $parser);
/*
    abstract protected function _uncachedMatch(
        $text, $pos=0,
        array &$cache,
        ParseError $error,
        \SplStack $stack
    );
 */
    /**
     * Return a parse tree of $text, starting at $pos.
     *
     * @throw ParseError if the expression wasn't satisfied.
     * @throw IncompleteParseError if the expression was satisfied,
     * but didn't consume the full string.
     *
     * @return Node
     */
/*
    public function parse($text, $pos=0)
    {
        $node = $this->match($text, $pos);
        if ($node->end < strlen($text)) {
            throw new IncompleteParseError($text, $node->end, $this);
        }

        return $node;
    }
 */
    /**
     * Return the parse tree matching this expression at the given position,
     * not necessarily extending all the way to the end of $text.
     *
     * @throw ParseError if there's no match there
     *
     * @return Node
     */
/*
    public function match($text, $pos=0, $parser)
    {
        return $this->_match($text, $pos, $parser);
    }

    protected function _match($text, $pos, array &$cache, ParseError $error,
                              \SplStack $stack)
    {
        $key = $this->id .'@' . $pos;
        $stack->push($this);
        if (!isset($cache[$key])) {
            $node = $this->_uncachedMatch($text, $pos, $cache, $error, $stack);
            $cache[$key] = $node;
        }
        $node = $cache[$key];
        if (!$node && $pos >= $error->pos
            && ($this->name || !$error->expr || !$error->expr->name)
        ) {
            $error->expr = $this;
            $error->pos = $pos;
        }
        $stack->pop();
        return $node;
    }
*/
    public function __toString()
    {
        return sprintf('<%s: %s>', get_class($this), $this->asRule());
    }

    public function asRule()
    {
        if ($this->name) {
            return sprintf('%s = %s', $this->name, $this->asRhs());
        }

        return $this->asRhs();
    }

}
