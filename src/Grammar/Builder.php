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

use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\EOF;
use ju1ius\Pegasus\Expression\Epsilon;
use ju1ius\Pegasus\Expression\Fail;
use ju1ius\Pegasus\Expression\Label;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Lookahead;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Expression\Regex;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\Skip;
use ju1ius\Pegasus\Expression\Wrapper;
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
     *
     * @var string
     */
    protected $currentRule;

    /**
     * Stack of added expressions.
     *
     * @var \SplStack<Expression>
     */
    protected $exprStack = null;

    /**
     * Builder constructor.
     *
     * @param Grammar|null $grammar
     */
    public function __construct(Grammar $grammar = null)
    {
        $this->grammar = $grammar ?: new Grammar();
        $this->exprStack = new \SplStack();
    }

    /**
     * @param Grammar|null $grammar
     *
     * @return Builder
     */
    public static function build(Grammar $grammar = null)
    {
        return new self($grammar);
    }

    /**
     * @return Grammar
     */
    public function getGrammar()
    {
        $this->end(true);

        return $this->grammar;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function rule($name)
    {
        $this->end(true);
        $this->currentRule = $name;

        return $this;
    }

    /**
     * @param Expression $expr
     *
     * @return $this
     */
    public function add(Expression $expr)
    {
        // stack is empty, we're at root
        if ($this->exprStack->isEmpty()) {
            $this->exprStack->push($expr);

            return $this;
        }

        $top = $this->exprStack->top();
        // if top expr is a wrapper and it has already one child, end the top expr
        if ($top instanceof Wrapper && count($top->children) === 1) {
            $this->end();
            $top = $this->exprStack->top();
        }

        if ($top instanceof Composite) {
            $top->children[] = $expr;
            $this->exprStack->push($expr);
        } else {
            throw new \RuntimeException(
                sprintf(
                    'Cannot add child expression %s to non-composite expression %s. Did you forget to call Builder::end()?',
                    $expr,
                    $top
                )
            );
        }

        return $this;
    }

    /**
     * @param bool $all
     *
     * @return $this
     */
    public function end($all = false)
    {
        $expr = null;

        if ($all) {
            while (!$this->exprStack->isEmpty()) {
                $expr = $this->exprStack->pop();
            }
        } else {
            $expr = $this->exprStack->pop();
        }

        if ($expr && $this->exprStack->isEmpty()) {
            $this->grammar[$this->currentRule] = $expr;
        }

        return $this;
    }

    /**
     * @param string $literal
     *
     * @return Builder
     */
    public function literal($literal)
    {
        $this->add(new Literal($literal));

        return $this->end();
    }

    /**
     * @param string $pattern
     * @param array  $flags
     *
     * @return Builder
     */
    public function regex($pattern, array $flags = [])
    {
        $this->add(new Regex($pattern, '', $flags));

        return $this->end();
    }

    /**
     * @return Builder
     */
    public function eof()
    {
        $this->add(new EOF());

        return $this->end();
    }

    /**
     * @return Builder
     */
    public function e()
    {
        $this->add(new Epsilon());

        return $this->end();
    }

    /**
     * @return Builder
     */
    public function fail()
    {
        $this->add(new Fail());

        return $this->end();
    }

    /**
     * @param string $name
     *
     * @return Builder
     */
    public function reference($name)
    {
        $this->add(new Reference($name));

        return $this->end();
    }

    /**
     * Alias of `reference`.
     *
     * @param string $name
     *
     * @return Builder
     */
    public function ref($name)
    {
        return $this->reference($name);
    }

    /**
     * @return Builder
     */
    public function sequence()
    {
        return $this->add(new Sequence());
    }

    /**
     * Alias of `sequence`
     *
     * @return Builder
     */
    public function seq()
    {
        return $this->sequence();
    }

    /**
     * @return Builder
     */
    public function oneOf()
    {
        return $this->add(new OneOf());
    }

    /**
     * Alias of `oneOf`.
     *
     * @return Builder
     */
    public function alt()
    {
        return $this->oneOf();
    }

    /**
     * @param int $min
     * @param int $max
     *
     * @return Builder
     */
    public function q($min = 0, $max = INF)
    {
        return $this->add(new Quantifier([], $min, $max));
    }

    /**
     * @param string $label
     *
     * @return Builder
     */
    public function label($label)
    {
        return $this->add(new Label([], $label));
    }

    /**
     * @return Builder
     */
    public function not()
    {
        return $this->add(new Not([]));
    }

    /**
     * @return Builder
     */
    public function lookahead()
    {
        return $this->add(new Lookahead([]));
    }

    /**
     * @return Builder
     */
    public function skip()
    {
        return $this->add(new Skip([]));
    }
}
