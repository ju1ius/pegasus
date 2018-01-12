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
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Exception\SelfReferencingRule;

class GrammarTraverser implements GrammarTraverserInterface
{
    /**
     * @var \SplObjectStorage|GrammarVisitorInterface[]
     */
    private $visitors;

    /**
     * @var bool
     */
    private $cloneExpressions;

    /**
     * @param bool $cloneExpressions Whether expressions must be cloned before traversal.
     */
    public function __construct(bool $cloneExpressions = true)
    {
        $this->cloneExpressions = $cloneExpressions;
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
            if ($rule instanceof Reference && $name === $rule->getIdentifier()) {
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

        return $grammar;
    }

    protected function traverseRule(Grammar $grammar, Expression $expr): Expression
    {
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

    protected function traverseExpression(Grammar $grammar, Expression $expr, $index = null, bool $isLast = false)
    {
        if ($this->cloneExpressions) {
            $expr = clone $expr;
        }

        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->enterExpression($expr, $index, $isLast)) {
                $expr = $result;
            }
        }

        if ($expr instanceof Composite) {
            $childCount = count($expr);
            foreach ($expr as $i => $child) {
                if (null !== $result = $this->traverseExpression($grammar, $child, $i, $i === $childCount - 1)) {
                    $expr[$i] = $result;
                }
            }
        }

        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->leaveExpression($expr, $index, $isLast)) {
                $expr = $result;
            }
        }

        return $expr;
    }
}
