<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Parser;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Trace\Trace;
use SplStack;

abstract class Parser
{
    /**
     * The grammar used to parse the input string.
     * @internal
     */
    public Grammar $grammar;

    /**
     * The input string.
     */
    protected string $source;

    /**
     * The current position into the input string.
     * @internal
     */
    public int $pos = 0;

    /** @internal */
    public SplStack $cutStack;
    /**
     * Stores named bindings produced by `Label` expressions.
     * @var SplStack<Scope>
     * @internal
     */
    public SplStack $scopes;

    /**
     * Flag that can be set by expressions to signal whether their children
     * should return parse nodes or just true on success.
     *
     * @internal
     */
    public bool $isCapturing = true;

    /**
     * @var Trace
     */
    protected Trace $trace;

    /**
     * The stack of currently applied grammar rules.
     *
     * @var SplStack<Expression>
     */
    protected SplStack $applicationStack;

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
     */
    final public function parse(string $source, ?string $startRule = null)
    {
        $this->isCapturing = true;

        return $this->doParse($source, 0, $startRule, false);
    }

    /**
     * Parse text starting from given position, using given start rule or the grammar's one,
     * but does not require the entire input to match the grammar.
     *
     * @api
     */
    final public function partialParse(string $text, int $pos = 0, ?string $startRule = null): Node|bool
    {
        $this->isCapturing = true;

        return $this->doParse($text, $pos, $startRule, true);
    }

    private function doParse(
        string $text,
        int $startPos,
        ?string $startRule = null,
        bool $allowPartial = false
    ): Node|bool {
        $this->source = $text;
        $this->pos = $startPos;
        $startRule = $startRule ?: $this->grammar->getStartRule();

        $this->beforeParse();

        $result = $this->apply($this->grammar[$startRule]);
        $parsedFully = $this->pos === \strlen($text);

        if (!$result || (!$parsedFully && !$allowPartial)) {
            $this->trace($startPos, $startRule);
            $this->afterParse($result);
            throw $this->trace->createParseError();
        }

        $this->afterParse($result);

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
     * This is called internally by `Reference` and `Super` expressions.
     * @internal
     */
    abstract public function apply(Expression $expr): Node|bool;

    /**
     * Evaluates an expression.
     */
    final public function evaluate(Expression $expr): Node|bool
    {
        $result = $expr->matches($this->source, $this);

        return $result;
    }

    /**
     * Called by Trace expressions before their child rule is evaluated
     * @internal
     */
    public function enterTrace(Expression $expr): void
    {
        $entry = $this->trace->push($expr);
        $entry->start = $this->pos;
    }

    /**
     * Called by Trace expressions after their child rule is evaluated
     * @internal
     * @param Expression $expr The expression being traced
     * @param Node|true|null $result The result of evaluating the traced expression
     */
    public function leaveTrace(Expression $expr, Node|bool $result): void
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
     */
    public function cut(int $position): void
    {
        $this->cutStack->pop();
        $this->cutStack->push(true);
    }

    /**
     * @internal
     */
    public function pushScope(): void
    {
        $current = $this->scopes->top();
        $this->scopes->push(clone $current);
    }

    /**
     * @internal
     */
    public function popScope(): void
    {
        $this->scopes->pop();
    }

    /**
     * @internal
     */
    public function bind(string $name, string $value): void
    {
        $scope = $this->scopes->top();
        $scope->bindings[$name] = $value;
    }

    /**
     * @internal
     */
    public function getBinding(string $name): ?string
    {
        $scope = $this->scopes->top();
        return $scope->bindings[$name] ?? null;
    }

    protected function beforeParse(): void
    {
        $this->trace = new Trace($this->source);
        $this->cutStack = new SplStack();
        $this->cutStack->push(false);
        $this->scopes = new SplStack();
        $this->scopes->push(new Scope());
    }

    protected function afterParse($result): void
    {
        while (!$this->scopes->isEmpty()) $this->scopes->pop();
        while (!$this->cutStack->isEmpty()) $this->cutStack->pop();
    }

    protected function trace(int $pos, ?string $startRule): void
    {
        $startRule = $startRule ?: $this->grammar->getStartRule();

        $this->grammar->tracing();
        $this->pos = $pos;
        $this->isCapturing = false;

        $this->beforeTrace();
        $this->apply($this->grammar[$startRule]);
        $this->afterTrace();

        $this->grammar->tracing(false);
    }

    protected function beforeTrace(): void
    {
        $this->cutStack = new SplStack();
        $this->cutStack->push(false);
        $this->scopes = new SplStack();
        $this->scopes->push(new Scope());
    }

    protected function afterTrace(): void
    {
        while (!$this->scopes->isEmpty()) $this->scopes->pop();
        while (!$this->cutStack->isEmpty()) $this->cutStack->pop();
    }
}
