<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Parser;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Exception\IncompleteParseError;
use ju1ius\Pegasus\Parser\Exception\ParseError;

abstract class Parser
{
    /**
     * The grammar used to parse the input string.
     *
     * @var Grammar
     */
    protected $grammar;

    /**
     * The input string.
     *
     * @var string
     */
    protected $source;

    /**
     * The current position into the input string.
     *
     * @var int
     */
    public $pos = 0;

    /**
     * Stores named bindings produced by `Label` expressions.
     *
     * @var array
     */
    public $bindings;

    /**
     * Flag that can be set by expressions to signal whether their children
     * should return parse nodes or just true on success.
     *
     * @var bool
     */
    public $isCapturing = true;

    /**
     * @var bool
     */
    public $isTracing = false;

    /**
     * @var ParseError
     */
    public $error;

    /**
     * The stack of currently applied grammar rules.
     *
     * @var \SplStack.<Expression>
     */
    protected $applicationStack;

    public function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;
    }

    /**
     * Parse the entire text, using given start rule or the grammar's one,
     * requiring the entire input to match the grammar.
     *
     * @param string $source
     * @param string|null $startRule
     *
     * @return Node|true|null
     */
    final public function parseAll(string $source, ?string $startRule = null)
    {
        $result = $this->parse($source, 0, $startRule);
        if ($this->pos < strlen($source)) {
            throw new IncompleteParseError(
                $source,
                $this->pos
            );
        }

        return $result;
    }

    /**
     * Parse text starting from given position, using given start rule or the grammar's one,
     * but does not require the entire input to match the grammar.
     *
     * @param string $text
     * @param int $pos
     * @param string $startRule
     *
     * @return Node|true|null
     */
    abstract public function parse(string $text, int $pos = 0, ?string $startRule = null);

    /**
     * Applies a grammar rule.
     *
     * This is called internally by `Reference` and `Super` expressions.
     *
     * @internal
     *
     * @param string $rule The rule name to apply
     * @param bool $super Whether we should explicitly apply a parent rule
     *
     * @return Node|null|true
     */
    abstract public function apply(string $rule, bool $super = false);

    /**
     * Evaluates an expression & updates current position on success.
     *
     * @param Expression $expr
     *
     * @return Node|null|true
     */
    final public function evaluate(Expression $expr)
    {
        $this->applicationStack->push($expr);
        $result = $expr->match($this->source, $this);
        $this->applicationStack->pop();

        return $result;
    }

    /**
     * Registers that the given expression failed to match at the given position.
     *
     * @param Expression $expr
     * @param int $pos
     */
    final public function registerFailure(Expression $expr, int $pos)
    {
        // We only care about the rightmost failure
        if ($pos > $this->error->position) {
            $this->error->position = $pos;
            $this->error->expr = $expr;
            $this->error->rule = $this->applicationStack->top();
        }
    }
}
