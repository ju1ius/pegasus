<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;


/**
 * This class provides a fluent interface for building grammars.
 *
 */
class Builder
{
    /**
     * Current rule name.
     */
    protected $current_rule = null;

    /**
     * Stack of added expressions.
     */
    protected $expr_stack = null;

    public function __construct(Grammar $grammar = null)
    {
        $this->grammar = $grammar ?: new Grammar();
        $this->expr_stack = new \SplStack();
    }

    public static function build(Grammar $grammar = null)
    {
        return new self($grammar);
    }

    public function getGrammar()
    {
        $this->end(true);

        return $this->grammar;
    }

    public function rule($name)
    {
        $this->end(true);
        $this->current_rule = $name;

        return $this;
    }

    public function add(Expression $expr)
    {
        // stack is empty, we're at root
        if ($this->expr_stack->isEmpty()) {
            $this->expr_stack->push($expr);

            return $this;
        }

        $top = $this->expr_stack->top();
        // if top expr is a wrapper and it has already one child
        // end the top expr 
        if ($top instanceof Expression\Wrapper && count($top->children) === 1) {
            $this->end();
            $top = $this->expr_stack->top();
        }

        if ($top instanceof Expression\Composite) {
            $top->children[] = $expr;
            $this->expr_stack->push($expr);
        } else {
            throw new \RuntimeException(sprintf(
                'Cannot add child expression %s to non-composite expression %s. Did you forget to call Builder::end()?',
                $expr,
                $top
            ));
        }

        return $this;
    }

    public function end($all = false)
    {
        $expr = null;

        if ($all) {
            while (!$this->expr_stack->isEmpty()) {
                $expr = $this->expr_stack->pop();
            }
        } else {
            $expr = $this->expr_stack->pop();
        }

        if ($expr && $this->expr_stack->isEmpty()) {
            $this->grammar[$this->current_rule] = $expr;
        }

        return $this;
    }

    public function literal($literal)
    {
        $this->add(new Expression\Literal($literal));

        return $this->end();
    }

    public function regex($pattern, $flags)
    {
        $this->add(new Expression\Regex($pattern, '', $flags));

        return $this->end();
    }

    public function eof()
    {
        $this->add(new Expression\EOF());

        return $this->end();
    }

    public function e()
    {
        $this->add(new Expression\Epsilon());

        return $this->end();
    }

    public function fail()
    {
        $this->add(new Expression\Fail());

        return $this->end();
    }

    public function ref($name)
    {
        $this->add(new Expression\Reference($name));

        return $this->end();
    }

    public function seq()
    {
        return $this->add(new Expression\Sequence());
    }

    public function oneOf()
    {
        return $this->add(new Expression\OneOf());
    }

    public function alt()
    {
        return $this->oneOf();
    }

    public function q($min = 0, $max = INF)
    {
        return $this->add(new Expression\Quantifier([], $min, $max));
    }

    public function label($label)
    {
        return $this->add(new Expression\Label([], $label));
    }

    public function not()
    {
        return $this->add(new Expression\Not());
    }

    public function lookahead()
    {
        return $this->add(new Expression\Lookahead());
    }

    public function skip()
    {
        return $this->add(new Expression\Skip());
    }
}
