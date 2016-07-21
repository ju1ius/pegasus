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

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator\Label;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Expression\Super;
use ju1ius\Pegasus\Grammar;

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
    public function canModifyBindings($ruleName)
    {
        foreach ($this->grammar[$ruleName]->iterate() as $expr) {
            if ($expr instanceof Label) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether a rule is regular (non-recursive).
     *
     * @param string $ruleName The rule name to analyze.
     *
     * @return bool
     */
    public function isRegular($ruleName)
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
     *
     * @todo Check that the super call is actually recursive when called like super::another_rule
     */
    public function isRecursive($ruleName)
    {
        return $this->containsSuperCall($ruleName) || $this->isReferencedFrom($ruleName, $ruleName);
    }

    /**
     * Returns whether a rule is left-recursive.
     *
     * @param string $ruleName The rule name to analyze.
     *
     * @return bool
     */
    public function isLeftRecursive($ruleName)
    {
        return $this->isLeftReferencedFrom($ruleName, $ruleName);
    }

    /**
     * Returns whether a rule is referenced by other rules in the grammar.
     *
     * @param string $ruleName The rule name to analyze.
     *
     * @return bool
     */
    public function isReferenced($ruleName)
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
    public function isReferencedFrom($referencer, $referencee)
    {
        foreach ($this->iterateReferences($referencer) as $name => $expr) {
            if ($name === $referencee) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether `referencer` left-references `referencee`, directly or indirectly.
     *
     * @param string $referencer The rule name to search in.
     * @param string $referencee The rule name to search for.
     *
     * @return bool
     */
    public function isLeftReferencedFrom($referencer, $referencee)
    {
        foreach ($this->iterateLeftReferences($referencer) as $name => $expr) {
            if ($name === $referencee) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a list of references contained by the given rule.
     *
     * @param string $ruleName The rule name to search in.
     *
     * @return array An array of reference names.
     */
    public function getReferencesFrom($ruleName)
    {
        $refs = iterator_to_array($this->iterateReferences($ruleName));

        return array_keys($refs);
    }

    /**
     * Returns a list of left references contained by the given rule.
     *
     * @param string $ruleName The rule name to search in.
     *
     * @return array An array of reference names.
     */
    public function getLeftReferencesFrom($ruleName)
    {
        $refs = iterator_to_array($this->iterateLeftReferences($ruleName));

        return array_keys($refs);
    }

    /**
     * Returns whether given rule contains a super call
     *
     * @param string $ruleName
     *
     * @return bool
     */
    protected function containsSuperCall($ruleName)
    {
        foreach ($this->grammar[$ruleName]->iterate() as $expr) {
            if ($expr instanceof Super) {
                return true;
            }
        }

        return false;
    }

    /**
     * Yields all references in the grammar, starting from the given rule.
     *
     * @param string $ruleName The rule to traverse
     * @param array  $visited  Set of already visited rules (recursion guard)
     *
     * @return \Generator
     */
    protected function iterateReferences($ruleName, array $visited = [])
    {
        $expr = $this->grammar[$ruleName];
        if (isset($visited[$expr->id])) {
            return;
        }
        $visited[$expr->id] = true;

        foreach ($this->iterateDirectReferences($expr) as $name => $expr) {
            yield $name => $expr;
            foreach ($this->iterateReferences($name, $visited) as $k => $v) {
                yield $k => $v;
            }
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
    protected function iterateLeftReferences($ruleName, array $visited = [])
    {
        $expr = $this->grammar[$ruleName];
        if (isset($visited[$expr->id])) {
            return;
        }
        $visited[$expr->id] = true;

        foreach ($this->iterateDirectLeftReferences($expr) as $name => $expr) {
            yield $name => $expr;
            foreach ($this->iterateLeftReferences($name, $visited) as $k => $v) {
                yield $k => $v;
            }
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
    protected function iterateDirectReferences(Expression $expr)
    {
        if ($expr instanceof Reference) {
            yield $expr->getIdentifier() => $expr;
        } elseif ($expr instanceof Composite) {
            foreach ($expr as $child) {
                // PLIZ I CAN HAZ yield from !!!
                foreach ($this->iterateDirectReferences($child) as $name => $expr) {
                    yield $name => $expr;
                }
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
    protected function iterateDirectLeftReferences(Expression $expr)
    {
        if ($expr instanceof Reference) {
            yield $expr->getIdentifier() => $expr;
        } elseif ($expr instanceof OneOf) {
            foreach ($expr as $child) {
                foreach ($this->iterateDirectLeftReferences($child) as $name => $expr) {
                    yield $name => $expr;
                }
            }
        } elseif ($expr instanceof Composite) {
            foreach ($this->iterateDirectLeftReferences($expr[0]) as $name => $expr) {
                yield $name => $expr;
            }
        }
    }

}
