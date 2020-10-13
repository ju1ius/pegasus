<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Application\Call;
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Expression\Application\Super;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator\Label;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Utils\Iter;


/**
 * Static analysis of a Grammar.
 */
class Analysis
{
    /**
     * @var Grammar
     */
    protected $grammar;

    /**
     * @param Grammar $grammar
     */
    public function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;
    }

    /**
     * @param string $ruleName
     *
     * @return bool
     */
    public function canModifyBindings(string $ruleName): bool
    {
        return Iter::some(function (Expression $expr) {
            return $expr instanceof Label;
        }, $this->grammar[$ruleName]->iterate());
    }

    /**
     * Returns whether a rule is regular (non-recursive).
     *
     * @param string $ruleName The rule name to analyze.
     *
     * @return bool
     */
    public function isRegular(string $ruleName): bool
    {
        return !$this->isRecursive($ruleName);
    }

    /**
     * Returns whether a rule is recursive
     * (contains a reference to itself, directly, indirectly or via a super call).
     *
     * @param string $ruleName The rule name to analyze.
     *
     * @return bool
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
     *
     * @param string $ruleName The rule name to analyze.
     *
     * @return bool
     */
    public function isLeftRecursive(string $ruleName): bool
    {
        return $this->isLeftReferencedFrom($ruleName, $ruleName);
    }

    /**
     * Returns whether a rule is referenced by other rules in the grammar.
     *
     * @param string $ruleName The rule name to analyze.
     *
     * @return bool
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
        return Iter::some(function (Expression $expr, string $name) use($referencee) {
            return $name === $referencee;
        }, $this->iterateReferences($referencer));
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
        return Iter::some(function (Expression $expr, string $name) use($referencee) {
            return $name === $referencee;
        }, $this->iterateLeftReferences($referencer));
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
     *
     * @param string $ruleName
     *
     * @return bool
     */
    protected function containsExternalCall(string $ruleName): bool
    {
        return Iter::some(function (Expression $expr) {
            return $expr instanceof Super || $expr instanceof Call;
        }, $this->grammar[$ruleName]->iterate());
    }

    /**
     * Yields all references in the grammar, starting from the given rule.
     *
     * @param string $ruleName The rule to traverse
     * @param array  $visited  Set of already visited rules (recursion guard)
     *
     * @return \Generator
     */
    protected function iterateReferences(string $ruleName, array $visited = []): \Generator
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
     * @param array  $visited  Set of already visited rules (recursion guard)
     *
     * @return \Generator
     */
    protected function iterateLeftReferences(string $ruleName, array $visited = []): \Generator
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
     * @return \Generator
     *
     * @todo handle Super calls
     */
    protected function iterateDirectReferences(Expression $expr): \Generator
    {
        if ($expr instanceof Reference) {
            yield $expr->getIdentifier() => $expr;
        } elseif ($expr instanceof Composite) {
            foreach ($expr as $child) {
                yield from $this->iterateDirectReferences($child);
            }
        }
    }

    /**
     * Generator yielding all left references in an expression.
     *
     * @param Expression $expr
     *
     * @return \Generator
     *
     * @todo handle Super calls
     */
    protected function iterateDirectLeftReferences(Expression $expr): \Generator
    {
        if ($expr instanceof Reference) {
            yield $expr->getIdentifier() => $expr;
        } elseif ($expr instanceof OneOf) {
            foreach ($expr as $child) {
                yield from $this->iterateDirectLeftReferences($child);
            }
        } elseif ($expr instanceof Composite) {
            yield from $this->iterateDirectLeftReferences($expr[0]);
        }
    }

}
