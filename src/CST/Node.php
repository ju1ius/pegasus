<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\CST;
use ju1ius\Pegasus\Utils\Str;


/**
 * Base class for Concrete-Syntax-Tree nodes.
 *
 * During parsing, A LOT of nodes are created, so they should be kept small and logic-less.
 * In particular no method call should happen inside the constructor, including calls to the parent constructor.
 *
 * Fields are public for performance, but should generally be treated as immutable once constructed.
 */
abstract class Node
{
    /**
     * The name of this node.
     *
     * @var string
     */
    public $name;

    /**
     * The position in the text where the expression started matching.
     *
     * @var int
     */
    public $start;

    /**
     * The position after start where the expression first didn't match.
     *
     * It represents the offset _after_ the match so it's typically equal to
     * `$this->start + strlen($this->value)`.
     *
     * @var int
     */
    public $end;

    /**
     * The value matched by this node (only for terminals).
     *
     * @var string|null
     */
    public ?string $value = null;

    /**
     * Optional attributes map.
     *
     * @var array
     */
    public $attributes;

    /**
     * Returns the text this node matched
     *
     * @param string $input The original input string
     *
     * @return string
     */
    final public function getText(string $input): string
    {
        $length = $this->end - $this->start;

        return $length ? substr($input, $this->start, $length) : '';
    }

    final public function __toString(): string
    {
        return sprintf(
            '%s#%s(%s) @[%d,%d] «%s»',
            Str::className($this, 1),
            spl_object_id($this),
            $this->name,
            $this->start,
            $this->end,
            (string)$this->value
        );
    }
}
