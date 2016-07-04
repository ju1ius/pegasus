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

use ju1ius\Pegasus\Expression\AttributedSequence;
use ju1ius\Pegasus\Expression\BackReference;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\EOF;
use ju1ius\Pegasus\Expression\Epsilon;
use ju1ius\Pegasus\Expression\Fail;
use ju1ius\Pegasus\Expression\Label;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Assert;
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Expression\NodeAction;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\Optional;
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Expression\RegExp;
use ju1ius\Pegasus\Expression\SemanticAction;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\Skip;
use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Expression\Token;
use ju1ius\Pegasus\Expression\ZeroOrMore;
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
    public static function create(Grammar $grammar = null)
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
        // if top expression is a `Decorator` and it has already one child, end the top expression
        if ($top instanceof Decorator && count($top) > 0) {
            $this->end();
            $top = $this->exprStack->top();
        }

        if ($top instanceof Composite) {
            $top[] = $expr;
            $this->exprStack->push($expr);
        } else {
            throw new \RuntimeException(
                sprintf(
                    'Cannot add child expression `%s` to non-composite expression `%s`.'
                    . ' Did you forget to call `Builder::end()`?',
                    get_class($expr),
                    get_class($top)
                )
            );
        }

        return $this;
    }

    /**
     * Ends the latest composite rule.
     *
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
        return $this->add(new Literal($literal))->end();
    }

    /**
     * @param string $pattern
     * @param array  $flags
     *
     * @return $this
     */
    public function match($pattern, array $flags = [])
    {
        return $this->add(new Match($pattern, $flags))->end();
    }

    /**
     * @param string $pattern
     * @param array  $flags
     *
     * @return $this
     */
    public function regexp($pattern, array $flags = [])
    {
        return $this->add(new RegExp($pattern, $flags))->end();
    }

    /**
     * @return $this
     */
    public function eof()
    {
        return $this->add(new EOF())->end();
    }

    /**
     * @return $this
     */
    public function epsilon()
    {
        return $this->add(new Epsilon())->end();
    }

    /**
     * Alias of `epsilon`.
     *
     * @return $this
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
        return $this->add(new Fail())->end();
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function reference($name)
    {
        return $this->add(new Reference($name))->end();
    }

    /**
     * Alias of `reference`.
     *
     * @param string $name
     *
     * @return $this
     */
    public function ref($name)
    {
        return $this->reference($name);
    }

    /**
     * @param $ref
     *
     * @return $this
     */
    public function backReference($ref)
    {
        return $this->add(new BackReference($ref))->end();
    }

    /**
     * Alias of `backReference`
     *
     * @param $ref
     *
     * @return Builder
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
     */
    public function seq()
    {
        return $this->sequence();
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
     */
    public function alt()
    {
        return $this->oneOf();
    }

    /**
     * @return $this
     */
    public function attributedSequence()
    {
        return $this->add(new AttributedSequence([]));
    }

    /**
     * Alias of `attributedSequence`.
     *
     * @return $this
     */
    public function attr()
    {
        return $this->attributedSequence();
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
        return $this->add(new Quantifier([], $min, $max));
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
        return $this->add(new Quantifier([], $n, $n));
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
        return $this->add(new Quantifier([], $n, INF));
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
        return $this->add(new Quantifier([], 0, $n));
    }

    /**
     * @return $this
     */
    public function optional()
    {
        return $this->add(new Optional([]));
    }

    /**
     * Alias of `optional`.
     *
     * @return $this
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
        return $this->add(new ZeroOrMore([]));
    }

    /**
     * @return $this
     */
    public function oneOrMore()
    {
        return $this->add(new OneOrMore([]));
    }

    //
    // Predicate Expressions
    // --------------------------------------------------------------------------------------------------------------

    /**
     * @return $this
     */
    public function not()
    {
        return $this->add(new Not([]));
    }

    /**
     * @return $this
     */
    public function assert()
    {
        return $this->add(new Assert([]));
    }

    //
    // Grouping Expressions
    // --------------------------------------------------------------------------------------------------------------

    /**
     * @return $this
     */
    public function skip()
    {
        return $this->add(new Skip([]));
    }

    /**
     * @return $this
     */
    public function token()
    {
        return $this->add(new Token([]));
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function label($label)
    {
        return $this->add(new Label([], $label));
    }

    //
    // Semantic Expressions
    // --------------------------------------------------------------------------------------------------------------

    /**
     * @param string $name
     * @param string $class
     *
     * @return $this
     */
    public function node($name = '', $class = '')
    {
        return $this->add(new NodeAction($name, $class))->end();
    }

    /**
     * @param \Closure $closure
     *
     * @return $this
     */
    public function semanticAction(\Closure $closure)
    {
        return $this->add(new SemanticAction($closure))->end();
    }

    /**
     * Alias of `semanticAction`.
     *
     * @param \Closure $closure
     *
     * @return Builder
     */
    public function action(\Closure $closure)
    {
        return $this->semanticAction($closure);
    }
}
