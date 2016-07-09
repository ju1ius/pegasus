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
use ju1ius\Pegasus\Parser\Scope;

/**
 * An object that matches against a piece of text.
 *
 */
abstract class Expression
{
    /**
     * A globally unique identifier for this expression.
     *
     * Used internally by the parsers for result memoization.
     * It is public for performance reasons and must NEVER be modified.
     *
     * We use an incrementing integer over spl_object_hash(),
     * because ATM it is significantly faster.
     *
     * @readonly
     * @internal
     *
     * @var integer
     */
    public $id;

    /**
     * The name of this expression.
     * Any named expression is turned into a grammar rule.
     *
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    private static $UID = 0;

    /**
     * @var string
     */
    protected static $DEFAULT_NODE_CLASS = Node::class;

    /**
     * Expression constructor.
     *
     * All subclasses MUST call their parent constructor.
     *
     * @param string $name Optional name for this expression.
     */
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
     * @param string          $text   The full text of the match subject.
     * @param int             $pos    The position at which this expression must start matching.
     * @param ParserInterface $parser The parser used for this expression.
     * @param Scope           $scope  The scope of bindings for the current sequence.
     *
     * @return Node|null
     */
    abstract public function match($text, $pos, ParserInterface $parser, Scope $scope);

    /**
     * @param string                     $text
     * @param int                        $pos
     * @param Grammar|array|\ArrayAccess $rules
     * @param Scope                      $scope
     *
     * @return mixed
     */
    //abstract public function parse($text, $pos, $rules, Scope $scope);

    /**
     * Returns a string representation of this expression, suitable for the right-hand-side of a rule.
     *
     * @return string
     */
    abstract public function __toString();

    public function asRule()
    {
        if ($this->name) {
            return sprintf('%s = %s', $this->name, $this->__toString());
        }

        return $this->__toString();
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
     * Returns whether it can be determined statically that the expression returns parse results on success.
     *
     * @return bool
     */
    public function isCapturingDecidable()
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
     * Returns whether this expression is a semantic action.
     *
     * @return bool
     */
    public function isSemantic()
    {
        return false;
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
