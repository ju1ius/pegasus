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
use ju1ius\Pegasus\Expression\Label;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Reference;
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
     * @var array
     */
    protected $references;

    /**
     * @var array
     */
    protected $leftReferences;

    /**
     * @var array
     */
    protected $directReferences;

    /**
     * @var array
     */
    protected $directLeftReferences;

    /**
     * Warning: The grammar must be unfolded before analysis !
     *
     * @param Grammar $grammar
     */
    public function __construct(Grammar $grammar)
    {
        if ($grammar->isFolded()) {
            throw new \RuntimeException(
                'A grammar cannot be analyzed in folded state. You must call Grammar::unfold() first.'
            );
        }
        $this->grammar = $grammar;
        $this->references = [];
        $this->leftReferences = [];
        $this->directReferences = [];
        $this->directLeftReferences = [];
    }

    /**
     * @param string $ruleName
     *
     * @return bool
     */
    public function needsScope($ruleName)
    {
        foreach ($this->grammar[$ruleName]->iterate() as $expr) {
            if ($expr instanceof Label) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns wheter a rule is regular (non-recursive).
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
     * Returns wheter a rule is recursive (non-regular).
     *
     * @param string $ruleName The rule name to analyze.
     *
     * @return bool
     */
    public function isRecursive($ruleName)
    {
        return $this->isReferencedFrom($ruleName, $ruleName);
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
        $startRule = $this->grammar->getStartRule()->getName();

        return $ruleName === $startRule || $this->isReferencedFrom($startRule, $ruleName);
    }

    /**
     * Returns whetner referencer has references for referencee.
     *
     * @param string $referencer The rule name to search in.
     * @param string $referencee The rule name to search for.
     *
     * @return bool
     */
    public function isReferencedFrom($referencer, $referencee)
    {
        $refs = $this->getReferencesFrom($referencer);

        return isset($refs[$referencee]);
    }

    /**
     * Returns wheter $referencer has left references for $referencee.
     *
     * @param string $referencer The rule name to search in.
     * @param string $referencee The rule name to search for.
     *
     * @return bool
     */
    public function isLeftReferencedFrom($referencer, $referencee)
    {
        $refs = $this->getLeftReferencesFrom($referencer);

        return isset($refs[$referencee]);
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
        if (!isset($this->references[$ruleName])) {
            $refs = [];
            $this->traceReferences($ruleName, [$this, 'directReferences'], $refs);
            $this->references[$ruleName] = $refs;
        }

        return $this->references[$ruleName];
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
        if (!isset($this->leftReferences[$ruleName])) {
            $refs = [];
            $this->traceReferences($ruleName, [$this, 'directLeftReferences'], $refs);
            $this->leftReferences[$ruleName] = $refs;
        }

        return $this->leftReferences[$ruleName];
    }

    /**
     * @param string   $ruleName
     * @param callable $tracer
     * @param array    &$refs
     */
    protected function traceReferences($ruleName, callable $tracer, &$refs)
    {
        $expr = $this->grammar[$ruleName];
        foreach ($tracer($expr) as $ref) {
            if (!isset($refs[$ref])) {
                $refs[$ref] = $ref;
                $this->traceReferences($ref, $tracer, $refs);
            }
        }
    }

    /**
     * Generator yielding all references in an expression.
     *
     * @param Expression $expr
     *
     * @return \Generator
     */
    protected function directReferences(Expression $expr)
    {
        if ($expr instanceof Reference) {
            yield $expr->identifier;
        } elseif ($expr instanceof Composite) {
            foreach ($expr as $child) {
                foreach ($this->directReferences($child) as $ref) {
                    yield $ref;
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
     */
    protected function directLeftReferences(Expression $expr)
    {
        if ($expr instanceof Reference) {
            yield $expr->identifier;
        } elseif ($expr instanceof OneOf) {
            foreach ($expr as $child) {
                foreach ($this->directLeftReferences($child) as $ref) {
                    yield $ref;
                }
            }
        } elseif ($expr instanceof Composite) {
            foreach ($this->directLeftReferences($expr[0]) as $ref) {
                yield $ref;
            }
        }
    }
}
