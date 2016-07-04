<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Traverser;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Exception\SelfReferencingRule;
use ju1ius\Pegasus\Visitor\ExpressionFolder;
use ju1ius\Pegasus\Visitor\GrammarVisitorInterface;

/**
 * Class for recursion-safe traversal of a grammar's expression graph.
 *
 * Before traversal:
 *   * Converts all rule references to Reference objects,
 *   * clone each expression
 *
 * After traversal:
 *   * Converts Reference objects back to actual expressions.
 *   * Adds all named expressions as rule to the grammar.
 *
 */
class GrammarTraverser implements GrammarTraverserInterface
{
    /**
     * @var \SplObjectStorage.<GrammarVisitorInterface>
     */
    private $visitors;

    /**
     * @var bool
     */
    private $foldGrammar = true;

    /**
     * @var bool
     */
    private $inTopLevelExpression = true;

    /**
     * @var bool
     */
    private $cloneExpressions;

    /**
     * Constructor for GrammarTraverser.
     *
     * If `$foldGrammar` if false, the references will not be converted back to actual expression objects.
     * This can be useful if you need ie to serialize the grammar in some way.
     *
     * @param bool $cloneExpressions Whether expressions must be cloned before traversal.
     * @param bool $foldGrammar      Whether grammars must be folded after traversal.
     *
     */
    public function __construct($cloneExpressions = true, $foldGrammar = false)
    {
        $this->cloneExpressions = $cloneExpressions;
        $this->foldGrammar = $foldGrammar;
        $this->visitors = new \SplObjectStorage();
    }

    /**
     * @inheritDoc
     */
    public function addVisitor(GrammarVisitorInterface ...$visitors)
    {
        foreach ($visitors as $visitor) {
            $this->visitors->attach($visitor);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeVisitor(GrammarVisitorInterface ...$visitors)
    {
        foreach ($visitors as $visitor) {
            $this->visitors->detach($visitor);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function traverse(Grammar $grammar)
    {
        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->beforeTraverse($grammar)) {
                $grammar = $result;
            }
        }

        foreach ($grammar as $name => $rule) {
            if ($rule instanceof Reference && $name === $rule->identifier) {
                throw new SelfReferencingRule($rule);
            }

            $result = $this->traverseRule($grammar, $rule);

            if (false === $result) {
                unset($grammar[$name]);
            } elseif (null !== $result) {
                $grammar[$name] = $result;
            }
        }

        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->afterTraverse($grammar)) {
                $grammar = $result;
            }
        }

        if ($this->foldGrammar) {
            // reference resolving has to be done in a full additional pass
            $resolver = (new ExpressionTraverser)->addVisitor(new ExpressionFolder($grammar));
            foreach ($grammar as $name => $rule) {
                $resolver->traverse($rule);
            }
        }

        return $grammar;
    }

    protected function traverseRule(Grammar $grammar, Expression $expr)
    {
        $this->inTopLevelExpression = true;

        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->enterRule($grammar, $expr)) {
                $expr = $result;
            }
        }

        if (null !== $result = $this->traverseExpression($grammar, $expr)) {
            $expr = $result;
        }

        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->leaveRule($grammar, $expr)) {
                $expr = $result;
            }
        }

        return $expr;
    }

    protected function traverseExpression(Grammar $grammar, Expression $expr)
    {
        // Convert all non top-level expressions to reference if needed,
        // in order to avoid infinite recursion in recursive rules.
        if (!$this->inTopLevelExpression && isset($grammar[$expr->name])) {
            $expr = new Reference($expr->name);
        } elseif ($this->cloneExpressions) {
            $expr = clone $expr;
        }
        $this->inTopLevelExpression = false;

        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->enterExpression($grammar, $expr)) {
                $expr = $result;
            }
        }

        if ($expr instanceof Composite) {
            foreach ($expr as $i => $child) {
                if (null !== $result = $this->traverseExpression($grammar, $child)) {
                    $expr[$i] = $result;
                }
            }
        }

        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->leaveExpression($grammar, $expr)) {
                $expr = $result;
            }
        }

        //FIXME: can we really modify the grammar while iterating ?
        if ($expr->name) {
            $grammar[$expr->name] = $expr;
        }

        return $expr;
    }
}
