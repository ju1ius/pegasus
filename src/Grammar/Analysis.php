<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Application\Call;
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Expression\Application\Super;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator\Bind;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Utils\Iter;

/**
 * Static analysis of a Grammar.
 */
class Analysis
{
    public function __construct(
        protected Grammar $grammar
    ) {
    }

    public function canModifyBindings(string $ruleName): bool
    {
        return Iter::some(
            fn(Expression $expr) => $expr instanceof Bind,
            $this->grammar[$ruleName]->iterate(),
        );
    }

    /**
     * Returns whether a rule is regular (non-recursive).
     */
    public function isRegular(string $ruleName): bool
    {
        return !$this->isRecursive($ruleName);
    }

    /**
     * Returns whether a rule is recursive
     * (contains a reference to itself, directly, indirectly or via a super call).
     */
    public function isRecursive(string $ruleName): bool
    {
        if ($this->containsExternalCall($ruleName)) {
            // TODO: check that the external call is actually recursive when called like super::another_rule
            return true;
        }
        // TODO: check imported grammars
        return $this->isReferencedFrom($ruleName, $ruleName);
    }

    /**
     * Returns whether a rule is left-recursive.
     */
    public function isLeftRecursive(string $ruleName): bool
    {
        return $this->isLeftReferencedFrom($ruleName, $ruleName);
    }

    /**
     * Returns whether a rule is referenced by other rules in the grammar.
     * @throws Exception\MissingStartRule
     */
    public function isReferenced(string $ruleName): bool
    {
        $startRule = $this->grammar->getStartRule();

        return $ruleName === $startRule || $this->isReferencedFrom($startRule, $ruleName);
    }

    /**
     * Returns whether `referencer` references `referencee`, directly or indirectly.
     *
     * @param string $referencer The rule name to search in.
     * @param string $referencee The rule name to search for.
     *
     * @return bool
     */
    public function isReferencedFrom(string $referencer, string $referencee): bool
    {
        return Iter::some(
            fn(Expression $expr, string $name) => $name === $referencee,
            $this->iterateReferences($referencer)
        );
    }

    /**
     * Returns whether `referencer` left-references `referencee`, directly or indirectly.
     *
     * @param string $referencer The rule name to search in.
     * @param string $referencee The rule name to search for.
     *
     * @return bool
     */
    public function isLeftReferencedFrom(string $referencer, string $referencee): bool
    {
        return Iter::some(
            fn(Expression $expr, string $name) => $name === $referencee,
            $this->iterateLeftReferences($referencer)
        );
    }

    /**
     * Returns a list of references contained by the given rule.
     *
     * @param string $ruleName The rule name to search in.
     *
     * @return string[] An array of reference names.
     */
    public function getReferencesFrom(string $ruleName): array
    {
        $refs = iterator_to_array($this->iterateReferences($ruleName));

        return array_keys($refs);
    }

    /**
     * Returns a list of left references contained by the given rule.
     *
     * @param string $ruleName The rule name to search in.
     *
     * @return string[] An array of reference names.
     */
    public function getLeftReferencesFrom(string $ruleName): array
    {
        $refs = iterator_to_array($this->iterateLeftReferences($ruleName));

        return array_keys($refs);
    }

    /**
     * Returns whether given rule contains a `Super` or `Call` expression
     */
    protected function containsExternalCall(string $ruleName): bool
    {
        return Iter::some(
            fn(Expression $expr) => $expr instanceof Super || $expr instanceof Call,
            $this->grammar[$ruleName]->iterate()
        );
    }

    /**
     * Yields all references in the grammar, starting from the given rule.
     *
     * @param string $ruleName The rule to traverse
     * @param array $visited Set of already visited rules (recursion guard)
     *
     * @return iterable<string, Expression>
     */
    protected function iterateReferences(string $ruleName, array $visited = []): iterable
    {
        $expr = $this->grammar[$ruleName];
        if (isset($visited[$expr->id])) {
            return;
        }
        $visited[$expr->id] = true;

        foreach ($this->iterateDirectReferences($expr) as $name => $expr) {
            yield $name => $expr;
            yield from $this->iterateReferences($name, $visited);
        }
    }

    /**
     * Yields all left-references in the grammar, starting from the given rule.
     *
     * @param string $ruleName The rule to traverse
     * @param array $visited Set of already visited rules (recursion guard)
     *
     * @return iterable<string, Expression>
     */
    protected function iterateLeftReferences(string $ruleName, array $visited = []): iterable
    {
        $expr = $this->grammar[$ruleName];
        if (isset($visited[$expr->id])) {
            return;
        }
        $visited[$expr->id] = true;

        foreach ($this->iterateDirectLeftReferences($expr) as $name => $expr) {
            yield $name => $expr;
            yield from $this->iterateLeftReferences($name, $visited);
        }
    }

    /**
     * Generator yielding all references in an expression.
     *
     * @param Expression $expr
     *
     * @return iterable<string, Expression>
     *
     * @todo handle Super calls
     */
    protected function iterateDirectReferences(Expression $expr): iterable
    {
        if ($expr instanceof Reference) {
            yield $expr->getIdentifier() => $expr;
        } else if ($expr instanceof Composite) {
            foreach ($expr as $child) {
                yield from $this->iterateDirectReferences($child);
            }
        }
    }

    /**
     * Generator yielding all left references in an expression.
     * @return iterable<string, Expression>
     * @throws Exception\RuleNotFound
     * @throws Exception\TraitNotFound
     */
    protected function iterateDirectLeftReferences(Expression $expr): iterable
    {
        if ($expr instanceof Reference) {
            yield $expr->getIdentifier() => $expr;
        } else if ($expr instanceof OneOf) {
            foreach ($expr as $child) {
                yield from $this->iterateDirectLeftReferences($child);
            }
        } else if ($expr instanceof Composite) {
            yield from $this->iterateDirectLeftReferences($expr[0]);
        } else if ($expr instanceof Super) {
            yield $expr->getIdentifier() => $expr;
            $analysis = new self($this->grammar->getParent());
            $super = $this->grammar->super($expr->getIdentifier());
            yield from $analysis->iterateDirectLeftReferences($super);
        } else if ($expr instanceof Call) {
            yield $expr->getIdentifier() => $expr;
            $trait = $this->grammar->getTrait($expr->getNamespace());
            $analysis = new self($trait);
            yield from $analysis->iterateDirectLeftReferences($trait[$expr->getIdentifier()]);
        }
    }
}
