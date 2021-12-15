<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;
use SplObjectStorage;

class ExpressionTraverser implements ExpressionTraverserInterface
{
    /**
     * @var SplObjectStorage<ExpressionVisitor>
     */
    protected SplObjectStorage $visitors;

    /**
     * Keeps track of the traversed expressions to avoid infinite recursion with recursive rules.
     */
    protected array $visited = [];

    public function __construct()
    {
        $this->visitors = new SplObjectStorage();
    }

    public function addVisitor(ExpressionVisitorInterface ...$visitors): static
    {
        foreach ($visitors as $visitor) {
            $this->visitors->attach($visitor);
        }

        return $this;
    }

    public function removeVisitor(ExpressionVisitorInterface ...$visitors): static
    {
        foreach ($visitors as $visitor) {
            $this->visitors->detach($visitor);
        }

        return $this;
    }

    public function traverse(Expression $expr): mixed
    {
        $this->visited = [];

        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->beforeTraverse($expr)) {
                $expr = $result;
            }
        }

        if (null !== $result = $this->traverseExpression($expr)) {
            $expr = $result;
        }

        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->afterTraverse($expr)) {
                $expr = $result;
            }
        }

        return $expr;
    }

    protected function traverseExpression(Expression $expr, $index = null, $isLast = false): Expression
    {
        foreach ($this->visitors as $visitor) {
            if (null !== $result = $visitor->enterExpression($expr, $index, $isLast)) {
                $expr = $result;
            }
        }

        if ($expr instanceof Composite) {
            $childCount = \count($expr);
            foreach ($expr as $i => $child) {
                // protect against recursive rules
                if (isset($this->visited[$child->id])) {
                    continue;
                }
                $this->visited[$child->id] = true;

                if (null !== $result = $this->traverseExpression($child, $i, $i === $childCount - 1)) {
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
