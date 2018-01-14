<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Expression\Application\Call;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Expression\Decorator\Assert;
use ju1ius\Pegasus\Expression\Decorator\Cut;
use ju1ius\Pegasus\Expression\Decorator\Label;
use ju1ius\Pegasus\Expression\Decorator\NodeAction;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\Decorator\OneOrMore;
use ju1ius\Pegasus\Expression\Decorator\Optional;
use ju1ius\Pegasus\Expression\Decorator\Quantifier;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Decorator\Token;
use ju1ius\Pegasus\Expression\Decorator\ZeroOrMore;
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Expression\Application\Super;
use ju1ius\Pegasus\Expression\Exception\CapturingGroupInMatchPattern;
use ju1ius\Pegasus\Expression\Terminal\BackReference;
use ju1ius\Pegasus\Expression\Terminal\EOF;
use ju1ius\Pegasus\Expression\Terminal\Epsilon;
use ju1ius\Pegasus\Expression\Terminal\Fail;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\Expression\Terminal\RegExp;
use ju1ius\Pegasus\Expression\Terminal\Word;
use ju1ius\Pegasus\RegExp\PCREGroupInfo;


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

    public function getExpression(): Expression
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
    public function literal(string $literal)
    {
        return $this->add(new Literal($literal));
    }

    /**
     * @param string $word
     *
     * @return $this
     */
    public function word(string $word)
    {
        return $this->add(new Word($word));
    }

    /**
     * @param string $pattern
     * @param array  $flags
     *
     * @return $this
     */
    public function match(string $pattern, array $flags = [])
    {
        $captureCount = PCREGroupInfo::captureCount($pattern);
        if ($captureCount > 0) {
            throw new CapturingGroupInMatchPattern($pattern, $captureCount);
        }
        return $this->add(new Match($pattern, $flags));
    }

    /**
     * @param string $pattern
     * @param array  $flags
     *
     * @return $this
     */
    public function regexp(string $pattern, array $flags = [])
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
    public function reference(string $name)
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
    public function ref(string $name)
    {
        return $this->reference($name);
    }

    /**
     * @param string $identifier
     *
     * @return $this
     */
    public function super(string $identifier)
    {
        return $this->add(new Super($identifier));
    }

    /**
     * @param string $namespace
     * @param string $identifier
     * @return $this
     */
    public function call(string $namespace, string $identifier)
    {
        return $this->add(new Call($namespace, $identifier));
    }

    /**
     * @param $ref
     *
     * @return $this
     */
    public function backReference(string $ref)
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
    public function backref(string $ref)
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
     * Adds a Quantifier matching between $min and $max terms ({min,max}).
     * Passing null to $max makes the quantifier unbounded.
     *
     * @param int $min
     * @param int|null $max
     *
     * @return $this
     */
    public function between(int $min = 0, ?int $max = null)
    {
        return $this->add(new Quantifier(null, $min, $max));
    }

    /**
     * Alias of `between`.
     *
     * @param int $min
     * @param int|null $max
     *
     * @return $this
     */
    public function q(int $min = 0, ?int $max = null)
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
    public function exactly(int $n)
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
    public function atLeast(int $n)
    {
        return $this->add(new Quantifier(null, $n));
    }

    /**
     * Adds a Quantifier matching at most $n terms ({0,n})
     *
     * @param int $n
     *
     * @return $this
     */
    public function atMost(int $n)
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
    public function ignore()
    {
        return $this->add(new Ignore());
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
    public function label(string $label)
    {
        return $this->add(new Label(null, $label));
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function named(string $name)
    {
        return $this->add(new NodeAction(null, $name));
    }

    //
    // Special Expressions
    // --------------------------------------------------------------------------------------------------------------

    public function cut()
    {
        return $this->add(new Cut());
    }
}
