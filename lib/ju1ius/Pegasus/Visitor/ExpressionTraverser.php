<?php

namespace ju1ius\Pegasus\Visitor;

use ju1ius\Pegasus\Expression;
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
			if (null !== $return = $visitor->beforeTraverse($expr)) {
				$expr = $return;
			}
		}

		$expr = $this->traverseExpression($expr);

		foreach ($this->visitors as $visitor) {
			if (null !== $return = $visitor->afterTraverse($expr)) {
				$expr = $return;
			}
		}

		return $expr;
	}

	protected function traverseExpression(Expression $expr)
	{
		foreach ($this->visitors as $visitor) {
			if (null !== $return = $visitor->enterNode($expr)) {
				$expr = $return;
			}
		}

		if ($expr instanceof Composite) {
			foreach ($expr->members as $i => $member) {
				// protect against recursive rules
				if (isset($this->visited[$member->id])) {
					//return $member;
					continue;
				}
				$this->visited[$member->id] = true;

				if (null !== $result = $this->traverseExpression($member)) {
					$expr->members[$i] = $result;
				}
			}
		}

		foreach ($this->visitors as $visitor) {
			if (null !== $return = $visitor->leaveNode($expr)) {
				$expr = $return;
			}
		}

		return $expr;
	}
}
