<?php

namespace ju1ius\Pegasus\Traverser;

use ju1ius\Pegasus\Visitor\ExpressionVisitorInterface;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Expression\Composite;


class ExpressionTraverser implements ExpressionTraverserInterface
{
	/**
	 * @var ExpressionVisitor[]
	 */
	protected $visitors;

	/**
	 * Keeps track of the traversed expression to avoid infinite recursion
	 * with recursive rules.
	 *
	 * @var array
	 */	
	protected $visited = [];

	public function __construct()
	{
		$this->visitors = [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function addVisitor(ExpressionVisitorInterface $visitor)
	{
		$this->visitors[] = $visitor;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeVisitor(ExpressionVisitorInterface $visitor)
	{
		foreach ($this->visitors as $index => $storedVisitor) {
			if ($storedVisitor === $visitor) {
				unset($this->visitors[$index]);
				break;
			}
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function traverse(Expression $expr)
	{
        $this->visited = [];

		foreach ($this->visitors as $visitor) {
			if (null !== $result = $visitor->beforeTraverse($expr)) {
				$expr = $result;
			}
		}

        if(null !== $result = $this->traverseExpression($expr)) {
            $expr = $result;
        }

		foreach ($this->visitors as $visitor) {
			if (null !== $result = $visitor->afterTraverse($expr)) {
				$expr = $result;
			}
		}

		return $expr;
	}

	protected function traverseExpression(Expression $expr)
    {
		foreach ($this->visitors as $visitor) {
			if (null !== $result = $visitor->enterExpression($expr)) {
				$expr = $result;
			}
		}

		if ($expr instanceof Composite) {
			foreach ($expr->members as $i => $member) {
				// protect against recursive rules
                if (isset($this->visited[$member->id])) {
                    continue;
                }
                $this->visited[$member->id] = true;

				if (null !== $result = $this->traverseExpression($member)) {
					$expr->members[$i] = $result;
				}
			}
		}

		foreach ($this->visitors as $visitor) {
			if (null !== $result = $visitor->leaveExpression($expr)) {
				$expr = $result;
			}
		}

		return $expr;
	}
}
