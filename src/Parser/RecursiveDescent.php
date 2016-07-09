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

class RecursiveDescent extends Parser
{
    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function parse($source, $pos = 0, $startRule = null)
    {
        $this->source = $source;
        $this->pos = $pos;
        $this->error = new ParseError($source);
        $this->error->rule = $startRule;

        //TODO solve the folded problem with scope
        $result = $this->grammar->unfolded(function (Grammar $grammar) use ($startRule, $pos) {
            $rule = $startRule ?: $grammar->getStartRule()->name;

            return $this->apply($rule, Scope::void());
        });

        if (!$result) {
            throw $this->error;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function apply($rule, Scope $scope)
    {
        $rule = $this->grammar[$rule];
        $this->error->position = $this->pos;
        $this->error->expr = $rule;

        return $this->evaluate($rule, $scope);
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
        $result = $expr->match($this->source, $this, $scope);
        if ($result) {
            $this->error->node = $result;
        }

        return $result;
    }
}
