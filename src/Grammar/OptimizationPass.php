<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Assert;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\Decorator\Skip;
use ju1ius\Pegasus\Expression\Decorator\Token;
use ju1ius\Pegasus\Grammar;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class OptimizationPass
{
    /**
     * @var \SplObjectStorage|Optimization[]
     */
    private $optimizations;

    /**
     * @var bool
     */
    private $cloneExpressions;

    /**
     * @param bool $cloneExpressions Whether expressions must be cloned before traversal.
     */
    public function __construct($cloneExpressions = false)
    {
        $this->cloneExpressions = $cloneExpressions;
        $this->optimizations = new \SplObjectStorage();
    }

    /**
     * @inheritDoc
     */
    public function add(Optimization ...$optimizations)
    {
        foreach ($optimizations as $optimization) {
            $this->optimizations->attach($optimization);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function remove(Optimization ...$optimizations)
    {
        foreach ($optimizations as $optimization) {
            $this->optimizations->detach($optimization);
        }

        return $this;
    }

    /**
     * @param Grammar             $grammar
     * @param OptimizationContext $context
     *
     * @return Grammar
     */
    public function process(Grammar $grammar, OptimizationContext $context = null)
    {
        $context = $context ?: OptimizationContext::of($grammar);

        foreach ($this->optimizations as $optimization) {
            if (null !== $result = $optimization->beforeTraverse($grammar)) {
                $grammar = $result;
            }
        }

        foreach ($grammar as $name => $rule) {
            $result = $this->processRule($grammar, $rule, $context);
            if ($result === false) {
                unset($grammar[$name]);
            } elseif ($result) {
                $grammar[$name] = $result;
            }
        }

        foreach ($this->optimizations as $optimization) {
            if (null !== $result = $optimization->afterTraverse($grammar)) {
                $grammar = $result;
            }
        }

        return $grammar;
    }

    /**
     * @param Grammar             $grammar
     * @param Expression          $expr
     * @param OptimizationContext $context
     *
     * @return Expression|false|null
     */
    protected function processRule(Grammar $grammar, Expression $expr, OptimizationContext $context)
    {
        foreach ($this->optimizations as $optimization) {
            if ($optimization->willPreProcessRule($grammar, $expr, $context)) {
                $result = $optimization->preProcessRule($grammar, $expr, $context);
                if ($result === false) {
                    return false;
                }
                if ($result) {
                    $expr = $result;
                }
            }
        }

        $result = $this->processExpression($grammar, $expr, $context);
        if ($result !== null) {
            $expr = $result;
        }

        foreach ($this->optimizations as $optimization) {
            if ($optimization->willPostProcessRule($grammar, $expr, $context)) {
                $result = $optimization->postProcessRule($grammar, $expr, $context);
                if ($result === false) {
                    return false;
                }
                if ($result) {
                    $expr = $result;
                }
            }
        }

        return $expr;
    }

    /**
     * @param Grammar             $grammar
     * @param Expression          $expr
     * @param OptimizationContext $context
     *
     * @return Expression
     */
    protected function processExpression(Grammar $grammar, Expression $expr, OptimizationContext $context)
    {
        if ($this->cloneExpressions) {
            $expr = clone $expr;
        }

        foreach ($this->optimizations as $optimization) {
            if ($optimization->willPreProcessExpression($expr, $context)) {
                $result = $optimization->preProcessExpression($expr, $context);
                if ($result !== null) {
                    $expr = $result;
                }
            }
        }

        if ($expr instanceof Composite) {
            foreach ($expr as $i => $child) {
                $result = $this->processExpression($grammar, $child, $this->getChildContext($expr, $context));
                if ($result !== null) {
                    $expr[$i] = $result;
                }
            }
        }

        foreach ($this->optimizations as $optimization) {
            if ($optimization->willPostProcessExpression($expr, $context)) {
                $result = $optimization->postProcessExpression($expr, $context);
                if ($result !== null) {
                    $expr = $result;
                }
            }
        }

        return $expr;
    }

    /**
     * @param Expression          $expr
     * @param OptimizationContext $context
     *
     * @return OptimizationContext
     */
    private function getChildContext(Expression $expr, OptimizationContext $context)
    {
        switch (get_class($expr)) {
            case Assert::class:
            case Not::class:
            case Token::class:
            case Skip::class:
                return $context->matching();
            default:
                return $context;
        }
    }
}
