<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Assert;
use ju1ius\Pegasus\Expression\BackReference;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Expression\EOF;
use ju1ius\Pegasus\Expression\Epsilon;
use ju1ius\Pegasus\Expression\Fail;
use ju1ius\Pegasus\Expression\Label;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Expression\Match\Word;
use ju1ius\Pegasus\Expression\NamedSequence;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\Optional;
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Expression\RegExp;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\Skip;
use ju1ius\Pegasus\Expression\Super;
use ju1ius\Pegasus\Expression\Token;
use ju1ius\Pegasus\Expression\ZeroOrMore;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class ExpressionBuilder
{
    /**
     * Stack of added composite expressions.
     *
     * @var \SplStack.<Composite>
     */
    protected $compositeStack;

    /**
     * The root expression of the tree we're building.
     *
     * @var Expression
     */
    protected $rootExpr;

    protected function __construct()
    {
        $this->compositeStack = new \SplStack();
    }

    /**
     * @return ExpressionBuilder
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @return Expression
     */
    public function getExpression()
    {
        $this->endAll();

        return $this->rootExpr;
    }

    /**
     * @param Expression $expr
     *
     * @return $this
     */
    public function add(Expression $expr)
    {
        // stack is empty, we're at root
        if ($this->compositeStack->isEmpty()) {
            if ($this->rootExpr) {
                throw new \RuntimeException(
                    'You cannot build more than one top-level expression.'
                    . ' Maybe you forgot to use a composite expression like `sequence` or `oneOf`?.'
                );
            }
            $this->rootExpr = $expr;
            if ($expr instanceof Composite) {
                $this->compositeStack->push($expr);
            }

            return $this;
        }

        $top = $this->compositeStack->top();
        // if top expression is a `Decorator` and it has already one child, end the top expression,
        // rinse and repeat for all parent decorators.
        while ($top instanceof Decorator && count($top) > 0) {
            $this->end();
            if ($this->compositeStack->isEmpty()) {
                break;
            }
            $top = $this->compositeStack->top();
        }

        // Add given expression as a child of the current parent.
        if ($top instanceof Composite) {
            $top[] = $expr;
        }

        // If given expression is a composite, push it onto the stack
        if ($expr instanceof Composite) {
            $this->compositeStack->push($expr);
        }

        return $this;
    }

    /**
     * Ends the current composite expression.
     *
     * @return $this
     */
    public function end()
    {
        if (!$this->compositeStack->isEmpty()) {
            $this->compositeStack->pop();
        }

        return $this;
    }

    /**
     * Ends all current composite expressions.
     *
     * @return $this
     */
    public function endAll()
    {
        while (!$this->compositeStack->isEmpty()) {
            $this->compositeStack->pop();
        }

        return $this;
    }

    //
    // Terminal Expressions
    // --------------------------------------------------------------------------------------------------------------

    /**
     * @param string $literal
     *
     * @return $this
     */
    public function literal($literal)
    {
        return $this->add(new Literal($literal));
    }

    /**
     * @param string $word
     *
     * @return $this
     */
    public function word($word)
    {
        return $this->add(new Word($word));
    }

    /**
     * @param string $pattern
     * @param array  $flags
     *
     * @return $this
     */
    public function match($pattern, array $flags = [])
    {
        return $this->add(new Match($pattern, $flags));
    }

    /**
     * @param string $pattern
     * @param array  $flags
     *
     * @return $this
     */
    public function regexp($pattern, array $flags = [])
    {
        return $this->add(new RegExp($pattern, $flags));
    }

    /**
     * @return $this
     */
    public function eof()
    {
        return $this->add(new EOF());
    }

    /**
     * @return $this
     */
    public function epsilon()
    {
        return $this->add(new Epsilon());
    }

    /**
     * Alias of `epsilon`.
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function e()
    {
        return $this->epsilon();
    }

    /**
     * @return $this
     */
    public function fail()
    {
        return $this->add(new Fail());
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function reference($name)
    {
        return $this->add(new Reference($name));
    }

    /**
     * Alias of `reference`.
     *
     * @param string $name
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function ref($name)
    {
        return $this->reference($name);
    }

    /**
     * @param string $identifier
     *
     * @return $this
     */
    public function super($identifier)
    {
        return $this->add(new Super($identifier));
    }

    /**
     * @param $ref
     *
     * @return $this
     */
    public function backReference($ref)
    {
        return $this->add(new BackReference($ref));
    }

    /**
     * Alias of `backReference`
     *
     * @param $ref
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function backref($ref)
    {
        return $this->backReference($ref);
    }

    //
    // Composite Expressions
    // --------------------------------------------------------------------------------------------------------------

    /**
     * @return $this
     */
    public function sequence()
    {
        return $this->add(new Sequence());
    }

    /**
     * Alias of `sequence`
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function seq()
    {
        return $this->sequence();
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function namedSequence($name)
    {
        return $this->add(new NamedSequence([], $name));
    }

    /**
     * Alias of `namedSequence`
     *
     * @param $name
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function named($name)
    {
        return $this->namedSequence($name);
    }

    /**
     * @return $this
     */
    public function oneOf()
    {
        return $this->add(new OneOf());
    }

    /**
     * Alias of `oneOf`.
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function alt()
    {
        return $this->oneOf();
    }

    //
    // Quantifier Expressions
    // --------------------------------------------------------------------------------------------------------------

    /**
     * Adds a Quantifier matching between $min and $max terms ({min,max})
     *
     * @param int $min
     * @param int $max
     *
     * @return $this
     */
    public function between($min = 0, $max = INF)
    {
        return $this->add(new Quantifier(null, $min, $max));
    }

    /**
     * Alias of `between`.
     *
     * @param int $min
     * @param int $max
     *
     * @return $this
     */
    public function q($min = 0, $max = INF)
    {
        return $this->between($min, $max);
    }

    /**
     * Adds a Quantifier matching exactly $n terms ({n,n})
     *
     * @param int $n
     *
     * @return $this
     */
    public function exactly($n)
    {
        return $this->add(new Quantifier(null, $n, $n));
    }

    /**
     * Adds a Quantifier matching at least $n terms ({n,})
     *
     * @param int $n
     *
     * @return $this
     */
    public function atLeast($n)
    {
        return $this->add(new Quantifier(null, $n, INF));
    }

    /**
     * Adds a Quantifier matching at most $n terms ({0,n})
     *
     * @param int $n
     *
     * @return $this
     */
    public function atMost($n)
    {
        return $this->add(new Quantifier(null, 0, $n));
    }

    /**
     * @return $this
     */
    public function optional()
    {
        return $this->add(new Optional());
    }

    /**
     * Alias of `optional`.
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function opt()
    {
        return $this->optional();
    }

    /**
     * @return $this
     */
    public function zeroOrMore()
    {
        return $this->add(new ZeroOrMore());
    }

    /**
     * @return $this
     */
    public function oneOrMore()
    {
        return $this->add(new OneOrMore());
    }

    //
    // Predicate Expressions
    // --------------------------------------------------------------------------------------------------------------

    /**
     * @return $this
     */
    public function not()
    {
        return $this->add(new Not());
    }

    /**
     * @return $this
     */
    public function assert()
    {
        return $this->add(new Assert());
    }

    //
    // Grouping Expressions
    // --------------------------------------------------------------------------------------------------------------

    /**
     * @return $this
     */
    public function skip()
    {
        return $this->add(new Skip());
    }

    /**
     * @return $this
     */
    public function token()
    {
        return $this->add(new Token());
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function label($label)
    {
        return $this->add(new Label(null, $label));
    }
}
