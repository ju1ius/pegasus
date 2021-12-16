<?php declare(strict_types=1);

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Grammar\Exception\AnonymousTopLevelExpression;
use ju1ius\Pegasus\Grammar\Exception\InvalidRuleType;
use ju1ius\Pegasus\Grammar\Exception\MissingStartRule;
use ju1ius\Pegasus\Grammar\Exception\MissingTraitAlias;
use ju1ius\Pegasus\Grammar\Exception\RuleNotFound;
use ju1ius\Pegasus\Grammar\Exception\TraitNotFound;
use ju1ius\Pegasus\Grammar\GrammarTraverser;
use ju1ius\Pegasus\Grammar\Optimizer;
use ju1ius\Pegasus\MetaGrammar\FileParser;
use ju1ius\Pegasus\MetaGrammar\MetaGrammarTransform;
use ju1ius\Pegasus\Parser\LeftRecursivePackratParser;
use ju1ius\Pegasus\Trace\GrammarTracer;
use Traversable;

/**
 * A collection of expressions that describe a language.
 */
class Grammar implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * The name of this grammar
     */
    protected string $name = '';

    /**
     * The parent grammar.
     */
    protected ?Grammar $parent = null;

    /**
     * Imported grammars
     *
     * @var Grammar[]
     */
    protected array $traits = [];

    /**
     * The start rule of this grammar.
     */
    protected ?string $startRule = null;

    /**
     * The rules used by this Grammar.
     *
     * @var Expression[]
     */
    protected array $rules = [];

    /**
     * List of rule names to be inlined during optimization.
     *
     * @var string[]
     */
    protected array $inlineRules = [];

    //
    // Factory methods
    // --------------------------------------------------------------------------------------------------------------

    /**
     * Factory method that constructs a Grammar object from a grammar file.
     * @throws MissingTraitAlias
     */
    public static function fromFile(string $path, int $optimizationLevel = 0): static
    {
        $grammar = (new FileParser())->parse($path);

        return $optimizationLevel
            ? Optimizer::optimize($grammar, $optimizationLevel)
            : $grammar;
    }

    /**
     * Factory method that constructs a Grammar object from an associative array of rules.
     *
     * @param Expression[] $rules An array of ['rule_name' => $expression].
     * @param ?string $startRule The top level expression of this grammar.
     * @param int $optimizationLevel
     * @throws MissingTraitAlias
     * @throws RuleNotFound
     */
    public static function fromArray(array $rules, ?string $startRule = null, int $optimizationLevel = 0): static
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
     * @param ?string $startRule Optional start rule name for the grammar.
     * @param int $optimizationLevel Optional optimization level.
     *
     * @return Grammar
     * @throws MissingTraitAlias
     */
    public static function fromSyntax(
        string $syntax,
        ?string $startRule = null,
        int $optimizationLevel = Optimizer::LEVEL_1
    ): static {
        $metaGrammar = MetaGrammar::create();
        $tree = (new LeftRecursivePackratParser($metaGrammar))->parse($syntax);
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
     * @param Expression $expr The expression to build the grammar from.
     * @param ?string $startRule Optional start rule name for the grammar.
     * @param int $optimizationLevel
     *
     * @return Grammar
     * @throws AnonymousTopLevelExpression If no named start rule could be determined.
     * @throws MissingTraitAlias
     */
    public static function fromExpression(
        Expression $expr,
        ?string $startRule = null,
        int $optimizationLevel = 0
    ): static {
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
     * @return $this
     */
    public function extends(Grammar $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent(): ?Grammar
    {
        return $this->parent;
    }

    /**
     * Explicitly fetch a rule from the parent grammar.
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
     * @return $this
     * @throws MissingTraitAlias
     */
    public function use(Grammar $other, ?string $as = null): static
    {
        $alias = $as ?: $other->getName();
        if (!$alias) {
            throw new MissingTraitAlias();
        }
        $this->traits[$alias] = $other;

        return $this;
    }

    /**
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the rules for this grammar, as a mapping from rule names to Expression objects.
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
     * @return $this
     * @throws RuleNotFound If the rule wasn't found in the grammar.
     */
    public function setStartRule(string $name): static
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
     * @throws MissingStartRule If no start rule was found.
     */
    public function getStartExpression(): Expression
    {
        return $this->rules[$this->getStartRule()];
    }

    /**
     * Mark given rule names as inlineable.
     *
     * @return $this
     */
    public function inline(string ...$ruleNames): static
    {
        foreach ($ruleNames as $ruleName) {
            $this->inlineRules[$ruleName] = true;
        }

        return $this;
    }

    /**
     * Returns whether the given rule is inlineable.
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
     * @return $this
     * @throws Grammar\Exception\SelfReferencingRule
     */
    public function tracing(bool $enable = true): static
    {
        if ($this->parent) {
            $this->parent->tracing($enable);
        }
        foreach ($this->traits as $trait) {
            $trait->tracing($enable);
        }
        // On failure, a parser will re-parse the input with tracing enabled for error-reporting.
        // Since we want to benefit from the memo table, we don't clone expressions
        // so that they keep their UIDs.
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
     * @throws Grammar\Exception\SelfReferencingRule
     */
    public function copy(bool $deep = false): static
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
     * @throws Grammar\Exception\SelfReferencingRule
     */
    public function merge(Grammar $other): static
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
     * @throws Grammar\Exception\SelfReferencingRule
     */
    public function map(callable $f): static
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
     * @throws Grammar\Exception\SelfReferencingRule
     */
    public function filter(callable $predicate): static
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
     * If the rule is not found in this grammar, tries to fallback to the parent grammar.
     * @param string $name
     */
    public function offsetExists(mixed $name): bool
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
     * If the rule is not found in this grammar, tries to fallback to the parent grammar.
     * @param string $name
     * @throws RuleNotFound
     */
    public function offsetGet(mixed $name): Expression
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
     * Value must be an Expression object.
     *
     * @param string $name
     * @param Expression $expr
     *
     * @throws InvalidRuleType If $expr is not an Expression.
     */
    public function offsetSet(mixed $name, mixed $expr): void
    {
        if (!$expr instanceof Expression) {
            throw new InvalidRuleType($expr);
        }

        $expr->setName($name);
        if (!$this->startRule) $this->startRule = $name;
        $this->rules[$name] = $expr;
    }

    /**
     * @param string $name
     */
    public function offsetUnset(mixed $name): void
    {
        unset($this->rules[$name]);
    }

    public function count(): int
    {
        return \count($this->rules);
    }

    public function getIterator(): Traversable
    {
        yield from $this->rules;
    }
}
