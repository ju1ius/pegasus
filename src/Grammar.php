<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Grammar\Exception\AnonymousTopLevelExpression;
use ju1ius\Pegasus\Grammar\Exception\MissingStartRule;
use ju1ius\Pegasus\Grammar\Exception\RuleNotFound;
use ju1ius\Pegasus\Grammar\Optimizer;
use ju1ius\Pegasus\Parser\LeftRecursivePackrat;
use ju1ius\Pegasus\Traverser\GrammarTraverser;
use ju1ius\Pegasus\Visitor\GrammarVisitor;
use ju1ius\Pegasus\Traverser\MetaGrammarTraverser;

/**
 * A collection of expressions that describe a language.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class Grammar implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * The name of this grammar
     *
     * @var string
     */
    protected $name = '';

    /**
     * The parent grammar.
     *
     * @var Grammar
     */
    protected $parent;

    /**
     * The start rule of this grammar.
     *
     * @var string
     */
    protected $startRule = null;

    /**
     * The rules used by this Grammar.
     *
     * @var Expression[]
     */
    protected $rules = [];

    /**
     * List of rule names to be inlined during optimization.
     *
     * @var string[]
     */
    protected $inlineRules = [];

    /**
     * True if the grammar is in folded state.
     *
     * @var bool
     */
    protected $folded = false;

    //
    // Factory methods
    // --------------------------------------------------------------------------------------------------------------

    /**
     * Factory method that constructs a Grammar object from an associative array of rules.
     *
     * @param Expression[] $rules     An array of ['rule_name' => $expression].
     * @param Expression   $startRule The top level expression of this grammar.
     *
     * @return Grammar
     */
    public static function fromArray(array $rules, $startRule = null)
    {
        $grammar = new static();
        foreach ($rules as $name => $rule) {
            $grammar[$name] = $rule;
        }
        if ($startRule) {
            $grammar->setStartRule($startRule);
        }

        return $grammar->unfold();
    }

    /**
     * Factory method that constructs a Grammar object from a syntax string.
     *
     * @param string $syntax
     * @param string $startRule Optional start rule name for the grammar.
     * @param int    $optimizationLevel Optional optimization level.
     *
     * @return Grammar
     * @throws Parser\Exception\IncompleteParseError
     */
    public static function fromSyntax($syntax, $startRule = null, $optimizationLevel = Optimizer::LEVEL_1)
    {
        $metaGrammar = MetaGrammar::create();
        $tree = (new LeftRecursivePackrat($metaGrammar))->parseAll($syntax);
        $grammar = (new MetaGrammarTraverser)->traverse($tree);
        if ($startRule) {
            $grammar->setStartRule($startRule);
        }

        return $optimizationLevel
            ? Optimizer::optimize($grammar, $optimizationLevel)
            : $grammar;
    }

    /**
     * Factory method that constructs a Grammar object from an Expression.
     *
     * @param Expression $expr              The expression to build the grammar from.
     * @param string     $startRule         Optional start rule name for the grammar.
     *
     * @return Grammar
     * @throws AnonymousTopLevelExpression If no named start rule could be determined.
     */
    public static function fromExpression(Expression $expr, $startRule = null, $optimizationLevel = Optimizer::LEVEL_1)
    {
        if (!$startRule) {
            if (!$expr->getName()) {
                throw new AnonymousTopLevelExpression($expr);
            }
            $startRule = $expr->getName();
        }

        $grammar = new static();
        $grammar[$startRule] = $expr;

        return $grammar->unfold();
    }

    /**
     * @param Grammar $parent
     *
     * @return $this
     */
    public function setParent(Grammar $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Grammar
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get the grammar's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the grammar's name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param $ruleName
     *
     * @return Builder
     */
    public function rule($ruleName)
    {
        return Builder::create($this)->rule($ruleName);
    }

    /**
     * Returns the rules for this grammar, as a mapping from rule names to Expression objects.
     *
     * @return Expression[]
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Sets the default start rule for this grammar.
     *
     * @param string $name The name of the rule to use as start rule.
     *
     * @return $this
     * @throws RuleNotFound If the rule wasn't found in the grammar.
     */
    public function setStartRule($name)
    {
        if (isset($this->rules[$name])) {
            $this->startRule = $name;

            return $this;
        }
        throw new RuleNotFound($name);
    }

    /**
     * Returns the start rule of this grammar.
     *
     * @return Expression
     * @throws MissingStartRule If no start rule was found.
     */
    public function getStartRule()
    {
        if (!$this->startRule) {
            throw new MissingStartRule();
        }

        return $this->rules[$this->startRule];
    }

    /**
     * Mark given rule names as inlineable.
     *
     * @param string[] ...$ruleNames
     *
     * @return $this
     */
    public function inline(...$ruleNames)
    {
        foreach ($ruleNames as $ruleName) {
            $this->inlineRules[$ruleName] = true;
        }

        return $this;
    }

    /**
     * Returns whether the given rule is inlineable.
     *
     * @param string $ruleName
     *
     * @return bool
     */
    public function isInlined($ruleName)
    {
        return isset($this->inlineRules[$ruleName]);
    }

    //
    // Grammar folding / unfolding
    // --------------------------------------------------------------------------------------------------------------

    /**
     * Returns whether the grammar is in folded state.
     *
     * @return bool True if the grammar is in folded state.
     */
    public function isFolded()
    {
        return $this->folded;
    }

    /**
     * Folds the grammar by resolving Reference objects
     * to actual references to the corresponding expressions.
     *
     * @param string $startRule An optional default start rule to use.
     *
     * @return $this
     */
    public function fold($startRule = null)
    {
        $traverser = (new GrammarTraverser(false, true))
            ->addVisitor(new GrammarVisitor);
        $traverser->traverse($this);

        if ($startRule) {
            $this->setStartRule($startRule);
        }

        $this->folded = true;

        return $this;
    }

    /**
     * Executes given function, while ensuring the grammar is folded.
     *
     * @param callable $fn
     *
     * @return mixed The result returned by the callback
     */
    public function folded(callable $fn)
    {
        $folded = $this->folded;
        if (!$folded) {
            $this->fold();
        }
        try {
            $result = $fn($this);
        } finally {
            if (!$folded) {
                $this->unfold();
            }
        }

        return $result;
    }

    /**
     * Unfolds the grammar by converting circular references to Reference objects.
     *
     * @return $this
     */
    public function unfold()
    {
        $traverser = (new GrammarTraverser(false, false))
            ->addVisitor(new GrammarVisitor);
        $traverser->traverse($this);

        $this->folded = false;

        return $this;
    }

    /**
     * Executes given function, while ensuring the grammar is unfolded.
     *
     * @param callable $fn
     *
     * @return mixed The result returned by the callback
     */
    public function unfolded(callable $fn)
    {
        $folded = $this->folded;
        if ($folded) {
            $this->unfold();
        }
        try {
            $result = $fn($this);
        } finally {
            if ($folded) {
                $this->fold();
            }
        }

        return $result;
    }

    /**
     * Prepares the grammar for matching.
     *
     * Folds the grammar and performs additional optimizations.
     *
     * @param string $startRule The default start rule to use.
     *
     * @return $this
     */
    public function finalize($startRule = null)
    {
        return $this->fold($startRule);
    }

    //
    // Grammar manipulations
    // --------------------------------------------------------------------------------------------------------------

    /**
     * Returns a clone of this Grammar.
     *
     * If deep is false, returns a shallow clone.
     * If deep is true, returns a deep clone, with all expressions cloned.
     *
     * @param bool $deep Whether to return a deep clone.
     *
     * @return Grammar
     */
    public function copy($deep = false)
    {
        $clone = clone $this;
        if ($deep) {
            $traverser = (new GrammarTraverser(true, $this->isFolded()))
                ->addVisitor(new GrammarVisitor);
            $traverser->traverse($clone);
        }

        return $clone;
    }

    /**
     * Returns a new (unfolded) grammar object containing the rules
     * of this instance merged with rules of $other.
     *
     * Rules with the same name will be overriden.
     *
     * @param Grammar $other The grammar to merge into this one.
     *
     * @return Grammar
     */
    public function merge(Grammar $other)
    {
        $new = $this->copy(true);
        $other = $other->copy(true);

        foreach ($other as $name => $rule) {
            $new[$name] = $rule;
        }

        return $new->unfold();
    }

    /**
     * Returns a copy of this grammar, with rules filtered by a predicate.
     *
     * @param callable $f `$f(Expression $expr, string $ruleName, Grammar $grammar)`
     *
     * @return Grammar
     */
    public function map(callable $f)
    {
        $new = $this->copy(true);
        foreach ($new->rules as $name => $expr) {
            $new[$name] = $f($expr, $name, $new);
        }

        return $new;
    }

    /**
     * Returns a shallow copy of this grammar, with rules filtered by a predicate.
     *
     * @param callable $predicate `$predicate(Expression $expr, string $ruleName, Grammar $grammar)`
     *
     * @return Grammar
     */
    public function filter(callable $predicate)
    {
        $new = $this->copy();
        foreach ($new->rules as $name => $expr) {
            if (!$predicate($expr, $name, $new)) {
                unset($new[$name]);
            }
        }

        return $new;
    }

    /**
     * Runs a reduce operation on this grammar's rules.
     *
     * @param callable $fn `$fn(mixed $accumulator, Expression $expr, string $ruleName, Grammar $grammar)`
     * @param mixed    $accumulator
     *
     * @return mixed
     */
    public function reduce(callable $fn, $accumulator = null)
    {
        foreach ($this->rules as $name => $expr) {
            $accumulator = $fn($accumulator, $expr, $name, $this);
        }

        return $accumulator;
    }

    /**
     * Returns a string representation of the grammar.
     * Should be as close as possible of the grammar's syntax.
     *
     * @return string
     */
    public function __toString()
    {
        $out = '';
        if ($name = $this->getName()) {
            $out .= "%name $name\n";
        }
        $out .= "%start {$this->startRule}\n";
        $out .= "\n";

        return $this->unfolded(function () use ($out) {
            foreach ($this->rules as $name => $expr) {
                $out .= sprintf("%s = %s\n", $name, $expr);
            }
            return $out;
        });
    }

    /**
     * Explicitely fetch a rule from the parent grammar.
     *
     * @param string $ruleName
     *
     * @return Expression
     */
    public function super($ruleName)
    {
        return $this->parent->offsetGet($ruleName);
    }

    //
    // SPL interfaces implementation
    // --------------------------------------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     * If the rule is not found in this grammar, tries to fallback to the parent grammar.
     *
     * @param string $name
     *
     * @return bool
     */
    public function offsetExists($name)
    {
        if (isset($this->rules[$name])) {
            return true;
        }
        if ($this->parent) {
            return $this->parent->offsetExists($name);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     * If the rule is not found in this grammar, tries to fallback to the parent grammar.
     *
     * @param string $name
     *
     * @return Expression
     * @throws RuleNotFound
     */
    public function offsetGet($name)
    {
        if (isset($this->rules[$name])) {
            return $this->rules[$name];
        }
        if ($this->parent) {
            return $this->parent->offsetGet($name);
        }

        throw new RuleNotFound($name);
    }

    /**
     * {@inheritdoc}
     * Value must be an Expression object.
     *
     * @param string     $name
     * @param Expression $expr
     *
     * @return Expression
     * @throws \InvalidArgumentException If $expr is not an Expression.
     */
    public function offsetSet($name, $expr)
    {
        if (!$expr instanceof Expression) {
            throw new \InvalidArgumentException(sprintf(
                'Value passed to `%s` must be instance of ju1ius\Pegasus\Expression, `%s` given.',
                __METHOD__,
                is_object($expr) ? get_class($expr) : gettype($expr)
            ));
        }

        $expr->setName($name);

        if (!$this->startRule) {
            $this->startRule = $name;
        }

        return $this->rules[$name] = $expr;
    }

    public function offsetUnset($name)
    {
        unset($this->rules[$name]);
    }

    public function count()
    {
        return count($this->rules);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->rules);
    }
}
