<?php

namespace ju1ius\Pegasus\Optimization;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;


/**
 * Static analysis of a Grammar.
 */
class Analysis
{
    protected $grammar;
    protected $references;
    protected $left_references;
    protected $direct_references;
    protected $direct_left_references;

    /**
     * Warning: The grammar must be unfolded before analysis !
     *
     * @param ju1ius\Pegasus\Grammar The grammar to analyse.
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
        $this->left_references = [];
        $this->direct_references = [];
        $this->direct_left_references = [];
    }

    /**
     * Returns wheter a rule is regular (non-recursive).
     *
     * @param string $rule_name The rule name to analyze.
     * 
     * @return bool
     */
    public function isRegular($rule_name)
    {
        return !$this->isRecursive($rule_name);
    }
    
    /**
     * Returns wheter a rule is recursive (non-regular).
     *
     * @param string $rule_name The rule name to analyze.
     * 
     * @return bool
     */
    public function isRecursive($rule_name)
    {
        return $this->isReferencedFrom($rule_name, $rule_name);
    }

    /**
     * Returns whether a rule is left-recursive.
     *
     * @param string $rule_name The rule name to analyze.
     * 
     * @return bool
     */
    public function isLeftRecursive($rule_name)
    {
        return $this->isLeftReferencedFrom($rule_name, $rule_name);
    }

    /**
     * Returns whether a rule is referenced by other rules in the grammar.
     *
     * @param string $rule_name The rule name to analyze.
     * 
     * @return bool
     */
    public function isReferenced($rule_name)
    {
        return $rule_name === $this->grammar->getStartRule()->name
            || $this->isReferencedFrom($rule_name, $rule_name)
        ;
    }
    
    /**
     * Returns wheter $referencer has references for $referencee.
     *
     * @param string    $referencer The rule name to search in.
     * @param string    $referencee The rule name to search for.
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
     * @param string    $referencer The rule name to search in.
     * @param string    $referencee The rule name to search for.
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
     * @param string    $rule_name The rule name to search in.
     *
     * @return array    An array of reference names.
     */
    public function getReferencesFrom($rule_name)
    {
        if (!isset($this->references[$rule_name])) {
            $refs = [];
            $this->traceReferences($rule_name, [$this, 'directReferences'], $refs);
            $this->references[$rule_name] = $refs;
        }
        return $this->references[$rule_name];
    }

    /**
     * Returns a list of left references contained by the given rule.
     *
     * @param string    $rule_name The rule name to search in.
     *
     * @return array    An array of reference names.
     */
    public function getLeftReferencesFrom($rule_name)
    {
        if (!isset($this->left_references[$rule_name])) {
            $refs = [];
            $this->traceReferences($rule_name, [$this, 'directLeftReferences'], $refs);
            $this->left_references[$rule_name] = $refs;
        }
        return $this->left_references[$rule_name];
    }

    protected function traceReferences($rule_name, callable $method, &$refs)
    {
        $expr = $this->grammar[$rule_name];
        foreach ($method($expr) as $ref) {
            if (!isset($refs[$ref])) {
                $refs[$ref] = $ref;
                $this->traceReferences($ref, $method, $refs);
            }   
        }
    }

    /**
     * Generator yielding all references in an expression.
     */
    protected function directReferences(Expression $expr)
    {
        if ($expr instanceof Expression\Reference) {
            yield $expr->identifier;
        } elseif ($expr instanceof Expression\Composite) {
            foreach ($expr->members as $member) {
                foreach ($this->directReferences($member) as $ref) {
                    yield $ref;
                }
            }
        }
    }

    /**
     * Generator yielding all left references in an expression.
     */
    protected function directLeftReferences(Expression $expr)
    {
        if ($expr instanceof Expression\Reference) {
            yield $expr->identifier;
        } elseif ($expr instanceof Expression\OneOf) {
            foreach ($expr->members as $member) {
                foreach ($this->directLeftReferences($member) as $ref) {
                    yield $ref;
                }
            }
        } elseif ($expr instanceof Expression\Composite) {
            foreach ($this->directLeftReferences($expr->members[0]) as $ref) {
                yield $ref;
            }
        }
    }
}
