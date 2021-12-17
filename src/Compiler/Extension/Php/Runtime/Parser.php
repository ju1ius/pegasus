<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Compiler\Extension\Php\Runtime;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use SplStack;

abstract class Parser
{
    protected string $source;
    protected int $pos = 0;
    protected bool $isCapturing = true;
    protected string $startRule;
    protected int $rightmostFailurePosition = 0;
    protected array $rightmostFailures = [];
    protected SplStack $cutStack;

    /**
     * Parse the entire text, using given start rule or the grammar's one,
     * requiring the entire input to match the grammar.
     */
    final public function parse(string $source, ?string $startRule = null): Node|bool
    {
        $this->isCapturing = true;
        return $this->doParse($source, 0, $startRule, false);
    }

    /**
     * Parse text starting from given position, using given start rule or the grammar's one,
     * but does not require the entire input to match the grammar.
     */
    final public function partialParse(string $source, int $pos = 0, ?string $startRule = null): Node|bool
    {
        $this->isCapturing = true;
        return $this->doParse($source, $pos, $startRule, true);
    }

    private function doParse(
        string $source,
        int $startPos,
        ?string $startRule = null,
        bool $allowPartial = false
    ): Node|bool {
        $this->source = $source;
        $this->pos = $startPos;
        $this->rightmostFailurePosition = 0;
        $startRule = $startRule ?: $this->startRule;

        $this->beforeParse();

        $result = $this->apply($startRule);
        $parsedFully = $this->pos === \strlen($source);

        if (!$result || (!$parsedFully && !$allowPartial)) {
            $this->afterParse($result);
            throw $this->createParseError();
        }

        $this->afterParse($result);

        return $result;
    }

    protected function apply(string $rule, array $bindings = []): Node|bool
    {
        return $this->{"match_{$rule}"}($bindings);
    }

    protected function registerFailure(string $rule, string $expr, int $pos)
    {
        if ($pos < $this->rightmostFailurePosition) return;
        $failure = ['rule' => $rule, 'expr' => $expr];
        if ($pos === $this->rightmostFailurePosition) {
            $this->rightmostFailures[] = $failure;
        } else {
            $this->rightmostFailurePosition = $pos;
            $this->rightmostFailures = [$failure];
        }
    }

    protected function cut(int $position): void
    {
        $this->cutStack->pop();
        $this->cutStack->push(true);
    }

    protected function beforeParse(): void
    {
        $this->cutStack = new SplStack();
        $this->cutStack->push(false);
        mb_ereg_search_init($this->source);
        mb_regex_set_options('z');
    }

    protected function afterParse($result): void
    {
    }

    protected function createParseError(): ParseError
    {
        $rules = array_map(fn($f) => $f['rule'], $this->rightmostFailures);
        return new ParseError(sprintf(
            'Failed to parse at position %d in rules: %s',
            $this->rightmostFailurePosition,
            implode(', ', $rules),
        ));
    }
}
