<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator\Assert;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\Decorator\Token;
use ju1ius\Pegasus\Grammar;
use SplObjectStorage;

class OptimizationPass
{
    /**
     * @var SplObjectStorage<Optimization>
     */
    private SplObjectStorage $optimizations;

    public function __construct(
        /**
         * Whether expressions must be cloned before traversal.
         */
        private bool $cloneExpressions = false
    ) {
        $this->optimizations = new SplObjectStorage();
    }

    /**
     * @return $this
     */
    public function add(Optimization ...$optimizations): static
    {
        foreach ($optimizations as $optimization) {
            $this->optimizations->attach($optimization);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function remove(Optimization ...$optimizations): static
    {
        foreach ($optimizations as $optimization) {
            $this->optimizations->detach($optimization);
        }

        return $this;
    }

    public function process(Grammar $grammar, ?OptimizationContext $context = null): Grammar
    {
        $context ??= OptimizationContext::of($grammar);

        foreach ($this->optimizations as $optimization) {
            if (null !== $result = $optimization->beforeTraverse($grammar, $context)) {
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
            if (null !== $result = $optimization->afterTraverse($grammar, $context)) {
                $grammar = $result;
            }
        }

        return $grammar;
    }

    protected function processRule(Grammar $grammar, Expression $expr, OptimizationContext $context): Expression|false|null
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

    protected function processExpression(Grammar $grammar, Expression $expr, OptimizationContext $context): Expression
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

    private function getChildContext(Expression $expr, OptimizationContext $context): OptimizationContext
    {
        return match ($expr::class) {
            Assert::class, Not::class, Token::class, Ignore::class => $context->matching(),
            default => $context,
        };
    }
}
