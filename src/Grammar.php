<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Grammar\Exception\InvalidRuleType;
use ju1ius\Pegasus\Grammar\Exception\MissingTraitAlias;
use ju1ius\Pegasus\Grammar\Exception\TraitNotFound;
use ju1ius\Pegasus\MetaGrammar\MetaGrammarTransform;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar\Exception\AnonymousTopLevelExpression;
use ju1ius\Pegasus\Grammar\Exception\MissingStartRule;
use ju1ius\Pegasus\Grammar\Exception\RuleNotFound;
use ju1ius\Pegasus\Grammar\GrammarTraverser;
use ju1ius\Pegasus\Grammar\Optimizer;
use ju1ius\Pegasus\Parser\LeftRecursivePackrat;
use ju1ius\Pegasus\Trace\GrammarTracer;


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
     * Imported grammars
     *
     * @var array
     */
    protected $traits = [];

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

    //
    // Factory methods
    // --------------------------------------------------------------------------------------------------------------

    /**
     * Factory method that constructs a Grammar object from an associative array of rules.
     *
     * @param Expression[] $rules An array of ['rule_name' => $expression].
     * @param string $startRule The top level expression of this grammar.
     * @param int $optimizationLevel
     *
     * @return Grammar
     * @throws RuleNotFound
     */
    public static function fromArray(array $rules, ?string $startRule = null, int $optimizationLevel = 0)
    {
        $grammar = new static();
        foreach ($rules as $name => $rule) {
            $grammar[$name] = $rule;
        }
        if ($startRule) {
            $grammar->setStartRule($startRule);
        }

        return $optimizationLevel
            ? Optimizer::optimize($grammar, $optimizationLevel)
            : $grammar;
    }

    /**
     * Factory method that constructs a Grammar object from a syntax string.
     *
     * @param string $syntax
     * @param string $startRule Optional start rule name for the grammar.
     * @param int $optimizationLevel Optional optimization level.
     *
     * @return Grammar
     * @throws Parser\Exception\IncompleteParseError
     * @throws MissingStartRule
     */
    public static function fromSyntax(
        string $syntax,
        ?string $startRule = null,
        int $optimizationLevel = Optimizer::LEVEL_1
    ) {
        $metaGrammar = MetaGrammar::create();
        $tree = (new LeftRecursivePackrat($metaGrammar))->parseAll($syntax);
        $grammar = (new MetaGrammarTransform)->transform($tree);
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
     * @param Expression $expr      The expression to build the grammar from.
     * @param string     $startRule Optional start rule name for the grammar.
     * @param int        $optimizationLevel
     *
     * @return Grammar
     * @throws AnonymousTopLevelExpression If no named start rule could be determined.
     */
    public static function fromExpression(
        Expression $expr,
        ?string $startRule = null,
        int $optimizationLevel = 0
    ) {
        if (!$startRule) {
            if (!$expr->getName()) {
                throw new AnonymousTopLevelExpression($expr);
            }
            $startRule = $expr->getName();
        }

        $grammar = new static();
        $grammar[$startRule] = $expr;

        return $optimizationLevel
            ? Optimizer::optimize($grammar, $optimizationLevel)
            : $grammar;
    }

    /**
     * @param Grammar $parent
     *
     * @return $this
     */
    public function extends(Grammar $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Grammar
     */
    public function getParent(): ?Grammar
    {
        return $this->parent;
    }

    /**
     * Explicitly fetch a rule from the parent grammar.
     *
     * @param string $ruleName
     *
     * @return Expression
     * @throws RuleNotFound
     */
    public function super(string $ruleName): Expression
    {
        return $this->parent->offsetGet($ruleName);
    }

    /**
     * Imports rules from another grammar, with an optional prefix.
     * Imported rules are accessible as `$grammar['prefix:ruleName']`.
     * If no prefix is given, the imported grammar name is used.
     *
     * @param Grammar $other
     * @param string|null $as
     * @return $this
     * @throws MissingTraitAlias
     */
    public function use(Grammar $other, ?string $as = null): self
    {
        $alias = $as ?: $other->getName();
        if (!$alias) {
            throw new MissingTraitAlias();
        }
        $this->traits[$alias] = $other;

        return $this;
    }

    /**
     * @param string $alias
     * @return Grammar
     * @throws TraitNotFound
     */
    public function getTrait(string $alias): Grammar
    {
        if (!isset($this->traits[$alias])) {
            throw new TraitNotFound($alias);
        }
        return $this->traits[$alias];
    }

    /**
     * @return Grammar[]
     */
    public function getTraits(): array
    {
        return $this->traits;
    }

    /**
     * Get the grammar's name.
     *
     * @return string
     */
    public function getName(): string
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
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the rules for this grammar, as a mapping from rule names to Expression objects.
     *
     * @return Expression[]
     */
    public function getRules(): array
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
    public function setStartRule(string $name): self
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
     * @return string
     * @throws MissingStartRule If no start rule was found.
     */
    public function getStartRule(): string
    {
        if (!$this->startRule) {
            throw new MissingStartRule();
        }

        return $this->startRule;
    }

    /**
     * Returns the start expression of this grammar.
     *
     * @return Expression
     * @throws MissingStartRule If no start rule was found.
     */
    public function getStartExpression(): Expression
    {
        return $this->rules[$this->getStartRule()];
    }

    /**
     * Mark given rule names as inlineable.
     *
     * @param string[] ...$ruleNames
     *
     * @return $this
     */
    public function inline(string ...$ruleNames): self
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
    public function isInlined(string $ruleName): bool
    {
        return isset($this->inlineRules[$ruleName]);
    }

    //
    // Grammar manipulations
    // --------------------------------------------------------------------------------------------------------------

    /**
     * Enables or disables the tracing of this grammar.
     *
     * @param bool $enable
     *
     * @return $this
     * @throws Grammar\Exception\SelfReferencingRule
     */
    public function tracing(bool $enable = true): Grammar
    {
        return (new GrammarTraverser(false))
            ->addVisitor(new GrammarTracer($enable))
            ->traverse($this);
    }

    /**
     * Returns a clone of this Grammar.
     *
     * If deep is false, returns a shallow clone.
     * If deep is true, returns a deep clone, with all expressions cloned.
     *
     * @param bool $deep Whether to return a deep clone.
     *
     * @return Grammar
     * @throws Grammar\Exception\SelfReferencingRule
     */
    public function copy(bool $deep = false): Grammar
    {
        $clone = clone $this;
        if ($deep) {
            return (new GrammarTraverser(true))->traverse($clone);
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
     * @throws Grammar\Exception\SelfReferencingRule
     */
    public function merge(Grammar $other): Grammar
    {
        $new = $this->copy(true);
        $other = $other->copy(true);

        foreach ($other as $name => $rule) {
            $new[$name] = $rule;
        }

        return $new;
    }

    /**
     * Returns a deep copy of this grammar, with rules returned by the given function.
     *
     * @param callable $f `$f(Expression $expr, string $ruleName, Grammar $grammar)`
     *
     * @return Grammar
     * @throws Grammar\Exception\SelfReferencingRule
     */
    public function map(callable $f): Grammar
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
     * @throws Grammar\Exception\SelfReferencingRule
     */
    public function filter(callable $predicate): Grammar
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
     * Returns a string representation of the grammar.
     * Should be as close as possible of the grammar's syntax.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function __toString(): string
    {
        $out = '';
        if ($name = $this->getName()) {
            $out .= "%name $name\n";
        }
        if (empty($this->rules)) {
            return $out;
        }
        $out .= "%start {$this->startRule}\n";
        $out .= "\n";

        foreach ($this->rules as $name => $expr) {
            if (isset($this->inlineRules[$name])) {
                $out .= '%inline ';
            }
            $out .= sprintf("%s = %s\n", $name, $expr);
        }

        return $out;
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
            throw new InvalidRuleType($expr);
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
