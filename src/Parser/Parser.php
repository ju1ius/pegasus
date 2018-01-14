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
use ju1ius\Pegasus\Trace\Trace;


abstract class Parser
{
    /**
     * The grammar used to parse the input string.
     *
     * @internal
     * @var Grammar
     */
    public $grammar;

    /**
     * The input string.
     *
     * @var string
     */
    protected $source;

    /**
     * The current position into the input string.
     *
     * @internal
     * @var int
     */
    public $pos = 0;

    /**
     * Stores named bindings produced by `Label` expressions.
     *
     * @internal
     * @var array
     */
    public $bindings;

    /**
     * @internal
     * @var \SplStack
     */
    public $cutStack;

    /**
     * Flag that can be set by expressions to signal whether their children
     * should return parse nodes or just true on success.
     *
     * @internal
     * @var bool
     */
    public $isCapturing = true;

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
     * @api
     * @param string $source
     * @param string|null $startRule
     *
     * @return Node|true|null
     * @throws Grammar\Exception\MissingStartRule
     */
    final public function parseAll(string $source, ?string $startRule = null)
    {
        $this->source = $source;
        $this->pos = 0;
        $startRule = $startRule ?: $this->grammar->getStartRule();
        $this->beforeParse();

        $result = $this->doParse($startRule);

        if (!$result) {
            $this->trace(0, $startRule);
            $this->afterParse($result);
            throw $this->trace->createParseError();
        }

        if ($this->pos < strlen($source)) {
            $this->trace(0, $startRule);
            $this->afterParse($result);
            throw $this->trace->createIncompleteParseError($this->pos);
        }

        $this->afterParse($result);

        return $result;
    }

    /**
     * Parse text starting from given position, using given start rule or the grammar's one,
     * but does not require the entire input to match the grammar.
     *
     * @api
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

        $this->beforeParse();
        $result = $this->doParse($startRule);

        if (!$result) {
            $this->trace($pos, $startRule);
            $this->afterParse($result);
            throw $this->trace->createParseError();
        }

        $this->afterParse($result);

        return $result;
    }

    private function doParse($startRule)
    {
        gc_disable();
        $result = $this->apply($this->grammar[$startRule]);
        gc_enable();

        return $result;
    }

    /**
     * @api
     * @return Trace
     */
    public function getTrace(): Trace
    {
        return $this->trace;
    }

    /**
     * Applies a grammar rule.
     *
     * This is called internally by `Reference` and `Super` expressions.
     *
     * @internal
     *
     * @param Expression $expr
     * @return Node|null|true
     */
    abstract public function apply(Expression $expr);

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

        return $result;
    }

    /**
     * Called by Trace expressions before their child rule is evaluated
     *
     * @internal
     * @param Expression $expr
     */
    public function enterTrace(Expression $expr): void
    {
        $entry = $this->trace->push($expr);
        $entry->start = $this->pos;
    }

    /**
     * Called by Trace expressions after their child rule is evaluated
     *
     * @internal
     * @param Expression $expr The expression being traced
     * @param Node|true|null $result The result of evaluating the traced expression
     */
    public function leaveTrace(Expression $expr, $result): void
    {
        if (!$result) {
            $this->trace->recordFailure($expr, $this->pos);
        }
        $entry = $this->trace->pop();
        $entry->end = $this->pos;
        $entry->result = $result;
    }

    /**
     * @internal
     * @param int $position
     */
    public function cut(int $position): void
    {
        $this->cutStack->pop();
        $this->cutStack->push(true);
    }

    protected function beforeParse(): void
    {
        $this->bindings = [];
        $this->isCapturing = true;
        $this->trace = new Trace($this->source);
        $this->cutStack = new \SplStack();
        $this->cutStack->push(false);
    }

    protected function afterParse($result): void
    {
        $this->bindings = [];
        //$this->cutStack = null;
    }

    protected function trace(int $pos, ?string $startRule)
    {
        $startRule = $startRule ?: $this->grammar->getStartRule();

        $this->grammar->tracing();
        $this->pos = $pos;
        $this->isCapturing = false;
        $this->bindings = [];
        $this->cutStack = new \SplStack();
        $this->cutStack->push(false);

        $this->beforeTrace();
        $this->apply($this->grammar[$startRule]);
        $this->afterTrace();

        $this->grammar->tracing(false);
    }

    protected function beforeTrace(): void {}

    protected function afterTrace(): void {}
}
