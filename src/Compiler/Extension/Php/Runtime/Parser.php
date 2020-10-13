<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler\Extension\Php\Runtime;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Exception\IncompleteParseError;
use ju1ius\Pegasus\Parser\Exception\ParseError;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class Parser
{
    /**
     * @var string
     */
    protected $source;

    /**
     * @var int
     */
    protected $pos = 0;

    /**
     * @var bool
     */
    protected $isCapturing = true;

    /**
     * @var string
     */
    protected $startRule;

    /**
     * @var int
     */
    protected $rightmostFailurePosition = 0;

    /**
     * @var array
     */
    protected $rightmostFailures = [];

    /**
     * @var \SplStack
     */
    protected $cutStack;

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
     * @param string $source
     * @param int $pos
     * @param string $startRule
     *
     * @return Node|bool
     */
    final public function partialParse(string $source, int $pos = 0, ?string $startRule = null)
    {
        $this->isCapturing = true;
        return $this->doParse($source, $pos, $startRule, true);
    }

    private function doParse(
        string $source,
        int $startPos,
        ?string $startRule = null,
        bool $allowPartial = false
    ) {
        $this->source = $source;
        $this->pos = $startPos;
        $this->rightmostFailurePosition = 0;
        $startRule = $startRule ?: $this->startRule;

        $this->beforeParse();
        gc_disable();

        $result = $this->apply($startRule);
        $parsedFully = $this->pos === strlen($source);

        if (!$result || (!$parsedFully && !$allowPartial)) {
            $this->afterParse($result);
            gc_enable();
            throw new ParseError();
        }

        $this->afterParse($result);
        gc_enable();

        return $result;
    }

    protected function apply(string $rule)
    {
        $matcher = "match_{$rule}";

        return $this->$matcher();
    }

    /**
     * @param string $rule
     * @param string $expr
     * @param int $pos
     */
    protected function registerFailure(string $rule, $expr, int $pos)
    {
        if ($pos >= $this->rightmostFailurePosition) {
            $this->rightmostFailurePosition = $pos;
            $rightmostFailures = $this->rightmostFailures[$pos] ?? [];
            $rightmostFailures[] = [
                'rule' => $rule,
                'expr' => $expr,
                'pos' => $pos,
            ];
            $this->rightmostFailures = $rightmostFailures;
        }
    }

    protected function cut(int $position)
    {
        $this->cutStack->pop();
        $this->cutStack->push(true);
    }

    protected function beforeParse() {
        $this->cutStack = new \SplStack();
        $this->cutStack->push(false);
    }

    protected function afterParse($result) {}
}
