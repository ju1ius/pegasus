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

use ju1ius\Pegasus\Parser\ParserInterface;

/**
 * An object that matches against a piece of text.
 *
 */
abstract class Expression
{
    /**
     * The name of this expression.
     *
     * @var string
     */
    public $name;

    /**
     * A globally unique identifier for this expression.
     *
     * It MUST never be modified, as it is used by the Parser classes
     * for caching match results.
     *
     * @var integer
     * @readonly
     * @internal
     */
    public $id;

    private static $UID = 0;

    public function __construct($name = '')
    {
        $this->name = $name;
        //$this->id = spl_object_hash($this);
        $this->id = ++self::$UID;
    }

    /**
     * Returns the parse tree matching this expression at the given position,
     * (not necessarily extending all the way to the end of $text),
     * or null if the match failed.
     *
     * This method is for internal use only and should never be called directly.
     *
     * @internal
     *
     * @param string          $text   The full text of the match subject.
     * @param int             $pos    The position at which this expression must start matching.
     * @param ParserInterface $parser The parser used for this expression.
     *
     * @return Node | null
     */
    abstract public function match($text, $pos, ParserInterface $parser);

    /**
     * Returns a string representation of this expression, suitable for the right-hand-side of a rule.
     *
     * @return string
     */
    abstract public function asRightHandSide();

    public function asRule()
    {
        if ($this->name) {
            return sprintf('%s = %s', $this->name, $this->asRightHandSide());
        }

        return $this->asRightHandSide();
    }

    public function __toString()
    {
        return sprintf('<%s: %s>', get_class($this), $this->asRule());
    }

    /**
     * Returns whether this expression is considered equal to another.
     *
     * @param Expression $other
     *
     * @return boolean
     */
    public function equals(Expression $other)
    {
        return $this instanceof $other
            && $other->id === $this->id
            && $other->name === $this->name;
    }

    /**
     * Returns whether the expression returns parse results on success,
     * or false if the expression simply returns true on success.
     *
     * @return bool
     */
    public function isCapturing()
    {
        return true;
    }

    /**
     * Returns whether the number of result nodes returned by the expression varies based on the input.
     *
     * @return bool
     */
    public function hasVariableCaptureCount()
    {
        return false;
    }

    /**
     * Returns whether it can be determined statically that the expression returns parse results on success.
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
