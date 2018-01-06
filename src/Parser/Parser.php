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
use ju1ius\Pegasus\Trace\Trace;


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
     * @var Trace
     */
    protected $trace;

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
     * @throws Grammar\Exception\MissingStartRule
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
     * @return Node|null|true
     * @throws Grammar\Exception\MissingStartRule
     */
    final public function parse(string $text, int $pos = 0, ?string $startRule = null)
    {
        $this->source = $text;
        $this->pos = $pos;
        $startRule = $startRule ?: $this->grammar->getStartRule();
        $this->bindings = [];
        $this->isCapturing = true;
        $this->trace = new Trace();
        $this->error = new ParseError($text, $pos);

        $this->beforeParse();

        //gc_disable();
        $result = $this->apply($startRule);
        //gc_enable();

        if (!$result) {
            throw $this->error;
        }

        $this->afterParse($result);

        return $result;
    }

    protected function beforeParse(): void {}

    protected function afterParse($result): void {}

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
     * Evaluates an expression.
     *
     * @param Expression $expr
     *
     * @return Node|null|true
     */
    final public function evaluate(Expression $expr)
    {
        $result = $expr->match($this->source, $this);
        // We only care about the rightmost failure
        if (!$result && $this->pos > $this->error->position) {
            $this->error->position = $this->pos;
            $this->error->expr = $expr;
        }

        return $result;
    }

    /**
     * Called by Trace expressions before their child rule is evaluated
     *
     * @param Expression $expr
     * @return mixed
     */
    public function enterTrace(Expression $expr)
    {
        $entry = $this->trace->push($expr);
        $entry->start = $this->pos;
    }

    /**
     * Called by Trace expressions after their child rule is evaluated
     *
     * @param Expression $expr The expression being traced
     * @param Node|true|null $result The result of evaluating the traced expression
     * @return mixed
     */
    public function leaveTrace(Expression $expr, $result)
    {
        $entry = $this->trace->pop();
        $entry->end = $this->pos;
        $entry->result = $result;
    }

    public function getTrace()
    {
        return $this->trace;
    }
}
