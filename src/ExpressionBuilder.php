<?php declare(strict_types=1);

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Expression\Application\Call;
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Expression\Application\Super;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Expression\Decorator\Assert;
use ju1ius\Pegasus\Expression\Decorator\Cut;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Decorator\Bind;
use ju1ius\Pegasus\Expression\Decorator\NodeAction;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\Decorator\OneOrMore;
use ju1ius\Pegasus\Expression\Decorator\Optional;
use ju1ius\Pegasus\Expression\Decorator\Quantifier;
use ju1ius\Pegasus\Expression\Decorator\Token;
use ju1ius\Pegasus\Expression\Decorator\ZeroOrMore;
use ju1ius\Pegasus\Expression\Exception\CapturingGroupInNonCapturingPattern;
use ju1ius\Pegasus\Expression\Terminal\Any;
use ju1ius\Pegasus\Expression\Terminal\BackReference;
use ju1ius\Pegasus\Expression\Terminal\CapturingRegExp;
use ju1ius\Pegasus\Expression\Terminal\EOF;
use ju1ius\Pegasus\Expression\Terminal\Epsilon;
use ju1ius\Pegasus\Expression\Terminal\Fail;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\NonCapturingRegExp;
use ju1ius\Pegasus\Expression\Terminal\Word;
use ju1ius\Pegasus\RegExp\PCREGroupInfo;
use SplStack;

class ExpressionBuilder
{
    /**
     * Stack of added composite expressions.
     * @var SplStack<Composite>
     */
    protected SplStack $compositeStack;

    /**
     * The root expression of the tree we're building.
     */
    protected ?Expression $rootExpr = null;

    protected function __construct()
    {
        $this->compositeStack = new SplStack();
    }

    public static function create(): static
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
     * @return $this
     */
    public function add(Expression $expr): static
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
        while ($top instanceof Decorator && \count($top) > 0) {
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
     * @return $this
     */
    public function end(): static
    {
        if (!$this->compositeStack->isEmpty()) {
            $this->compositeStack->pop();
        }

        return $this;
    }

    /**
     *
     * @return $this
     */
    public function endAll(): static
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
     * @return $this
     */
    public function literal(string $literal): static
    {
        return $this->add(new Literal($literal));
    }

    /**
     * @return $this
     */
    public function any(): static
    {
        return $this->add(new Any());
    }

    /**
     * @return $this
     */
    public function word(string $word): static
    {
        return $this->add(new Word($word));
    }

    /**
     * @param string $pattern
     * @param string[]  $flags
     * @return $this
     */
    public function match(string $pattern, array $flags = []): static
    {
        $captureCount = PCREGroupInfo::captureCount($pattern);
        if ($captureCount > 0) {
            throw new CapturingGroupInNonCapturingPattern($pattern, $captureCount);
        }
        return $this->add(new NonCapturingRegExp($pattern, $flags));
    }

    /**
     * @param string $pattern
     * @param string[]  $flags
     * @return $this
     */
    public function regexp(string $pattern, array $flags = []): static
    {
        return $this->add(new CapturingRegExp($pattern, $flags));
    }

    /**
     * @return $this
     */
    public function eof(): static
    {
        return $this->add(new EOF());
    }

    /**
     * @return $this
     */
    public function epsilon(): static
    {
        return $this->add(new Epsilon());
    }

    /**
     * Alias of `epsilon`.
     * @return $this
     * @codeCoverageIgnore
     */
    public function e(): static
    {
        return $this->epsilon();
    }

    /**
     * @return $this
     */
    public function fail(): static
    {
        return $this->add(new Fail());
    }

    /**
     * @return $this
     */
    public function reference(string $name): static
    {
        return $this->add(new Reference($name));
    }

    /**
     * Alias of `reference`.
     * @return $this
     * @codeCoverageIgnore
     */
    public function ref(string $name): static
    {
        return $this->reference($name);
    }

    /**
     * @return $this
     */
    public function super(string $identifier): static
    {
        return $this->add(new Super($identifier));
    }

    /**
     * @return $this
     */
    public function call(string $namespace, string $identifier): static
    {
        return $this->add(new Call($namespace, $identifier));
    }

    /**
     * @return $this
     */
    public function backReference(string $ref): static
    {
        return $this->add(new BackReference($ref));
    }

    /**
     * Alias of `backReference`
     * @return $this
     * @codeCoverageIgnore
     */
    public function backref(string $ref): static
    {
        return $this->backReference($ref);
    }

    //
    // Composite Expressions
    // --------------------------------------------------------------------------------------------------------------

    /**
     * @return $this
     */
    public function sequence(): static
    {
        return $this->add(new Sequence());
    }

    /**
     * Alias of `sequence`
     * @return $this
     * @codeCoverageIgnore
     */
    public function seq(): static
    {
        return $this->sequence();
    }

    /**
     * @return $this
     */
    public function oneOf(): static
    {
        return $this->add(new OneOf());
    }

    /**
     * Alias of `oneOf`.
     * @return $this
     * @codeCoverageIgnore
     */
    public function alt(): static
    {
        return $this->oneOf();
    }

    //
    // Quantifier Expressions
    // --------------------------------------------------------------------------------------------------------------

    /**
     * Adds a Quantifier matching between $min and $max terms ({min,max}).
     * Passing null to $max makes the quantifier unbounded.
     * @return $this
     */
    public function between(int $min = 0, ?int $max = null): static
    {
        return $this->add(new Quantifier(null, $min, $max));
    }

    /**
     * Alias of `between`.
     * @return $this
     */
    public function q(int $min = 0, ?int $max = null): static
    {
        return $this->between($min, $max);
    }

    /**
     * Adds a Quantifier matching exactly $n terms ({n,n})
     * @return $this
     */
    public function exactly(int $n): static
    {
        return $this->add(new Quantifier(null, $n, $n));
    }

    /**
     * Adds a Quantifier matching at least $n terms ({n,})
     * @return $this
     */
    public function atLeast(int $n): static
    {
        return $this->add(new Quantifier(null, $n));
    }

    /**
     * Adds a Quantifier matching at most $n terms ({0,n})
     * @return $this
     */
    public function atMost(int $n): static
    {
        return $this->add(new Quantifier(null, 0, $n));
    }

    /**
     * @return $this
     */
    public function optional(): static
    {
        return $this->add(new Optional());
    }

    /**
     * Alias of `optional`.
     * @return $this
     * @codeCoverageIgnore
     */
    public function opt(): static
    {
        return $this->optional();
    }

    /**
     * @return $this
     */
    public function zeroOrMore(): static
    {
        return $this->add(new ZeroOrMore());
    }

    /**
     * @return $this
     */
    public function oneOrMore(): static
    {
        return $this->add(new OneOrMore());
    }

    //
    // Predicate Expressions
    // --------------------------------------------------------------------------------------------------------------

    /**
     * @return $this
     */
    public function not(): static
    {
        return $this->add(new Not());
    }

    /**
     * @return $this
     */
    public function assert(): static
    {
        return $this->add(new Assert());
    }

    //
    // Grouping Expressions
    // --------------------------------------------------------------------------------------------------------------

    /**
     * @return $this
     */
    public function ignore(): static
    {
        return $this->add(new Ignore());
    }

    /**
     * @return $this
     */
    public function asToken(): static
    {
        return $this->add(new Token());
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function bindTo(string $label): static
    {
        return $this->add(new Bind($label, null));
    }

    /**
     * @return $this
     */
    public function named(string $name): static
    {
        return $this->add(new NodeAction(null, $name));
    }

    //
    // Special Expressions
    // --------------------------------------------------------------------------------------------------------------

    public function cut(): static
    {
        return $this->add(new Cut());
    }
}
