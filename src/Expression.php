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

use ju1ius\Pegasus\Parser\Parser;
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
    protected $name;

    /**
     * @var int
     */
    private static $UID = 0;

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
     * @param string $text   The full text of the match subject.
     * @param Parser $parser The parser used for this expression.
     * @param Scope  $scope  The scope of bindings for the current sequence.
     *
     * @return Node|null
     * @internal param int $pos The position at which this expression must start matching.
     */
    abstract public function match($text, Parser $parser, Scope $scope);

    /**
     * Returns a string representation of this expression, suitable for the right-hand-side of a rule.
     *
     * @return string
     */
    abstract public function __toString();

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param bool $depthFirst
     *
     * @return \Generator
     */
    public function iterate($depthFirst = false)
    {
        yield $this;
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
