<?php

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Exception\GrammarException;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Visitor\ExpressionTraverser;
use ju1ius\Pegasus\Visitor\RuleCollector;
use ju1ius\Pegasus\Visitor\ReferenceResolver;


/**
 * The Abstract class which all grammar extends.
 *
 */
class AbstractGrammar implements \ArrayAccess, \Countable, \IteratorAggregate
{
	/**
	 * @var Expression[] The rules used by this Grammar.
	 */
	protected $rules = [];

	/**
	 * @var string The default start rule of the grammar.
	 */
	protected $default_rule = null;

    /**
     * Returns the rules for this grammar, as a mapping
     * between from rule names to Expression objects.
     *
     * @return Expression[]
     */
    public function getRules()
    {
        return $this->rules;
    }
    
	/**
	 * Sets the default start rule for this grammar.
	 *
	 * @param string $name The name of the rule to use as start rule.
	 *
	 * @return $this
	 */
	public function setStartRule($name)
	{
		if (isset($this->rules[$name])) {
			$this->default_rule = $name;

			return $this;
		}
		throw new GrammarException(
			"The rule '$name' wasn't found in this grammar."
		);
	}

	/**
	 * Retunrns the default start rule of this grammar.
	 *
	 * @return Expression
	 */
	public function getStartRule()
	{
		if (!$this->default_rule) {
			throw new GrammarException(
				'You must provide a start rule for the grammar.'
			);
		}
		return $this->rules[$this->default_rule];
	}

	/**
	 * Merges this grammar with another.
	 *
	 * Rules with the same name will be overriden.
	 *
	 * @param Grammar	$other	The grammar to merge into this one.
	 *
	 * @return $this
	 */
    public function merge(Grammar $other)
    {
        foreach ($other as $name => $rule) {
            $this->rules[$name] = $rule;
        }

        return $this;
    }

	/**
	 * Finalizes a hand crafted grammar
	 * by collecting all named rules and resolving all references.
	 *
	 * This method MUST be called after constructing a grammar manually.
	 * It is called under the hood by Grammar::fromSyntax and Grammar::fromExpression
	 *
	 * @return $this
	 */
	public function finalize($start_rule = null)
	{
		$collector = new RuleCollector($this);
		$traverser = (new ExpressionTraverser)->addVisitor($collector);
		foreach ($this->rules as $name => $expr) {
			$traverser->traverse($expr);
		}

		$traverser->removeVisitor($collector)
			->addVisitor(new ReferenceResolver($this))
		;
		foreach ($this->rules as $name => $expr) {
			$this->rules[$name] = $traverser->traverse($expr);
		}

	   	if ($start_rule) {
			$this->setStartRule($start_rule);
		}

		return $this;
	}
    
    public function __toString()
    {
		$start_rule = $this->getStartRule();
		$exprs = [
			$this->default_rule . ' = ' . $start_rule->asRhs()
		];

        foreach ($this->rules as $name => $expr) {
			if ($name === $this->default_rule) {
				continue;
			}
			$exprs[] = $name . ' = ' . $expr->asRhs();
        }

        return implode("\n", $exprs);
    }

    public function offsetExists($name)
    {
        return isset($this->rules[$name]);
    }

    public function offsetGet($name)
    {
        return $this->rules[$name];
    }

    public function offsetSet($name, $expr)
    {
        if (!$expr instanceof Expression) {
			throw new GrammarException(sprintf(
                'Value passed to %s must be instance of ju1ius\Pegasus\Expression, "%s" given.',
                __METHOD__,
                is_object($expr) ? get_class($expr) : gettype($expr)
            ));
        }
		if (!$expr->name) {
			$expr->name = $name;
		} elseif ($expr->name !== $name) {
			//throw new GrammarException(sprintf(
				//'Index "%s" doesn\'t match expression name "%s"',
				//$name, $expr->name
			//));
		}
        $this->rules[$name] = $expr;
		//if ($this->default_rule && $name === $this->default_rule->name) {
			//$this->default_rule = $expr;
		//}
    }

    public function offsetUnset($name)
    {
        unset($this->rules[$name]);
    }

    public function count()
    {
        return count($this->rules);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->rules);
    }
}
