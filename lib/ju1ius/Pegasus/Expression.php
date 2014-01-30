<?php

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Exception\IncompleteParseError;
use ju1ius\Pegasus\Exception\ParseError;
use ju1ius\Pegasus\Parser\ParserInterface;


/**
 * A thing that can be matched against a piece of text.
 *
 **/
abstract class Expression
{
    /**
     * The name of this expression.
     *
     * @var string
     */
    public $name;

    /**
     * A globally unique identifier for this expression,
     * generated by spl_object_hash().
     *
     * It MUST never be modified, as it is used by the Parser classes
     * for caching match results.
     *
     * @internal
     * @var string
     */
    public $id;

    private static $UID = 0;

    public function __construct($name='')
    {
        $this->name = $name;
        //$this->id = spl_object_hash($this);
        $this->id = ++self::$UID;
    }

    abstract public function asRhs();

    /**
     * Returns the parse tree matching this expression at the given position,
     * (not necessarily extending all the way to the end of $text),
     * or null if the match failed.
     *
     * @param string                    $text The full text of the match subject.
     * @param int                       $pos The position at which this expression must the match.
     * @param Parser\ParserInterface    $parser The parser used for this expression.
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
        return $this instanceof $other
            && $other->id === $this->id
            && $other->name === $this->name
        ;
    }

    /**
     * Returns  true if the expression returns parse results on success,
     * or false if the expression simply returns true on success.
     *
     * @return bool
     */
    public function isCapturing()
    {
        return true;
    }

    /**
     * Returns true if the number of result nodes returned by the expression
     * varies based on the input.
     *
     * @return bool
     */
    public function hasVariableCaptureCount()
    {
        return false;
    }
    /**
     * Returns true if it can be determined statically whether the expression
     * returns parse results on success.
     *
     * @return bool
     */
    public function isCapturingDecidable()
    {
        return true;
    }
    

    public function __clone()
    {
        //$this->id = spl_object_hash($this);
        $this->id = ++self::$UID;
    }
    
    public function __wakeup()
    {
        //$this->id = spl_object_hash($this);
        $this->id = ++self::$UID;
    }
}
