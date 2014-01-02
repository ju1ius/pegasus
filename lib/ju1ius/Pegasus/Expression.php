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

    /**
     * Return the parse tree matching this expression at the given position,
     * not necessarily extending all the way to the end of $text.
     *
     * @return Node | null
     */
    abstract public function match($text, $pos, ParserInterface $parser);

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
    
    /**
     * Documentation for equals
     *
     * @param Expression $other
     * @return void
     */
    public function equals(Expression $other)
    {
        return $other instanceof $this
            && $other->id === $this->id;
    }
    
}
