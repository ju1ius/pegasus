<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Parser;

use ju1ius\Pegasus\Exception\IncompleteParseError;
use ju1ius\Pegasus\Exception\ParseError;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Node;

class RecursiveDescent implements ParserInterface
{
    /**
     * @var Grammar
     */
    protected $grammar;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var int
     */
    public $pos = 0;

    /**
     * @var ParseError
     */
    protected $error;

    public function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;
    }

    /**
     * Return the parse tree matching this expression at the given position,
     * not necessarily extending all the way to the end of $text.
     *
     * @param string      $source
     * @param string|null $rule
     *
     * @return Node
     *
     * @throws IncompleteParseError
     * @throws ParseError if there's no match there
     * @throws null
     */
    public function parseAll($source, $rule = null)
    {
        $result = $this->parse($source, 0, $rule);
        if ($this->pos < strlen($source)) {
            throw new IncompleteParseError(
                $source,
                $this->pos,
                $this->error
            );
        }

        return $result;
    }

    /**
     * Return the parse tree matching this expression at the given position,
     * not necessarily extending all the way to the end of $text.
     *
     * @param string $source
     * @param int    $pos
     * @param string $startRule
     *
     * @return Node|null
     *
     * @throw ParseError if there's no match there
     * @throws null
     */
    public function parse($source, $pos = 0, $startRule = null)
    {
        $this->source = $source;
        $this->pos = $pos;
        $this->error = new ParseError($source);
        $this->error->rule = $startRule;

        $result = $this->grammar->folded(function (Grammar $grammar) use ($startRule, $pos) {
            $rule = $startRule ? $grammar[$startRule] : $grammar->getStartRule();

            return $this->apply($rule, $pos, Scope::void());
        });

        if (!$result) {
            throw $this->error;
        }

        return $result;
    }

    public function apply(Expression $expr, $pos, Scope $scope)
    {
        $this->pos = $pos;
        $this->error->position = $pos;
        $this->error->expr = $expr;

        // evaluate expression
        $result = $this->evaluate($expr, $scope);

        return $result;
    }

    public function applyRule($ruleName, $pos, Scope $scope)
    {
        $this->error->rule = $ruleName;
        return $this->apply($this->grammar[$ruleName], $pos, $scope);
    }

    /**
     * Evaluates an expression & updates current position on success.
     *
     * @param Expression $expr
     *
     * @param Scope      $scope
     *
     * @return Node|null
     */
    public function evaluate(Expression $expr, Scope $scope)
    {
        $result = $expr->match($this->source, $this->pos, $this, $scope);
        if ($result) {
            // update parser position
            $this->pos = $result->end;
            $this->error->node = $result;
        }

        return $result;
    }
}
