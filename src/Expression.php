<?php declare(strict_types=1);

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Parser;
use Stringable;

/**
 * An object that matches against a piece of text.
 */
abstract class Expression implements Stringable
{
    /**
     * A globally unique identifier for this expression.
     *
     * Used internally by the parsers for result memoization.
     * It is public for performance reasons and must NEVER be modified.
     *
     * We use an incrementing integer over spl_object_hash(),
     * because ATM it is significantly faster.
     * @todo use spl_object_id in php >= 7.2
     *
     * @readonly
     * @internal
     */
    public int $id;

    /**
     * The name of this expression.
     * Any named expression is turned into a grammar rule.
     */
    protected string $name;

    private static int $UID = 0;

    /**
     * Expression constructor.
     *
     * All subclasses MUST call their parent constructor.
     *
     * @param string $name Optional name for this expression.
     */
    public function __construct(string $name = '')
    {
        $this->name = $name;
        //$this->id = spl_object_id($this);
        $this->id = ++self::$UID;
    }

    /**
     * Tries to match this expression against the given text.
     * Returns either:
     *   - a `Node` instance if the match succeeded and the expression is capturing
     *   - `true` if the match succeeded and the expression is not capturing
     *   - `false` if the match failed
     *
     * @param string $text The full text of the match subject.
     * @param Parser $parser The parser used for this expression.
     *
     * @return Node|bool
     */
    abstract public function matches(string $text, Parser $parser): Node|bool;

    /**
     * Returns a string representation of this expression, suitable for the right-hand-side of a rule.
     */
    abstract public function __toString(): string;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Iterates over the expression tree.
     *
     * @param bool $depthFirst Whether to yield child expressions before their parent.
     *
     * @return iterable<Expression>
     */
    public function iterate(?bool $depthFirst = false): iterable
    {
        yield $this;
    }

    /**
     * Returns whether the expression returns parse results on success,
     * or false if the expression simply returns true on success.
     *
     * @return bool
     */
    public function isCapturing(): bool
    {
        return true;
    }

    /**
     * Returns whether it can be determined statically that the expression returns parse results on success.
     *
     * @return bool
     */
    public function isCapturingDecidable(): bool
    {
        return true;
    }

    /**
     * Returns whether the number of result nodes returned by the expression varies based on the input.
     *
     * @return bool
     */
    public function hasVariableCaptureCount(): bool
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
