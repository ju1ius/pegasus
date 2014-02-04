<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Exception\GrammarException;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Traverser\GrammarTraverser;
use ju1ius\Pegasus\Visitor\GrammarVisitor;
use ju1ius\Pegasus\Visitor\MetaGrammarNodeVisitor;
use ju1ius\Pegasus\Parser\LRPackrat as Parser;


/**
 * A collection of expressions that describe a language.
 *
 * <code>
 * use ju1ius\Pegasus\Grammar;
 * use ju1ius\Pegasus\Parser\Packrat as Parser;
 * // or if the grammar is left-recursive:
 * // use ju1ius\Pegasus\Parser\LRPackrat as Parser
 * 
 * $syntax = <<<'EOS'
 * polite_greeting = greeting ", my good sir"
 * greeting = Hi / Hello
 * EOS;
 * 
 * $grammar = Grammar::fromSyntax($syntax);
 * $parse_tree = (new Parser($grammar))->parseAll('Hello, my good sir');
 * </code>
 *
 * Or start parsing from any of the other expressions.
 *
 * <code>
 * $parse_tree = (new Parser($grammar))->parseAll('Hi', 'greeting');
 * </code>
 *
 * You can also just construct a bunch of Expression objects yourself
 * and stitch them together into a language by using:
 * <code>
 * Grammar::fromExpression($my_expression);
 * </code>
 * But using a Grammar has some important advantages:
 *
 */
class Grammar implements GrammarInterface
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
     * @var bool True if the grammar is in folded state.
     */
    protected $folded = true;

    /**
     * @var string The name of the grammar
     */
    protected $name = '';

	/**
	 * Factory method that constructs a Grammar object from an associative array of rules.
	 *
	 * @param Expression[]	$rules		An array of ['rule_name' => $expression].
	 * @param Expression	$start_rule	The top level expression of this grammar.
	 **/
    public static function fromArray(array $rules, $start_rule = null)
    {
        $g = new static();
        foreach ($rules as $name => $rule) {
            $g[$name] = $rule;
        }
        if ($start_rule) {
            $g->setStartRule($start_rule);
        }

        return $g->unfold();
    }
    
	/**
	 * Factory method that constructs a Grammar object from a syntax string.
	 *
	 * @param string	$syntax
     * @param string    $start_rule Optional start rule name for the grammar.
	 *
	 * @return Grammar
	 */
	public static function fromSyntax($syntax, $start_rule=null)
	{
		$metagrammar = MetaGrammar::create();
		$tree = (new Parser($metagrammar))->parseAll($syntax);
		$grammar = (new MetaGrammarNodeVisitor)->visit($tree);
        if ($start_rule) {
            $grammar->setStartRule($start_rule);
        }

        return $grammar->unfold();
	}

	/**
	 * Factory method that constructs a Grammar object from an Expression.
	 *
     * @param Expression    $expr The expression to build the grammar from.
     * @param string        $start_rule Optional start rule name for the grammar.
	 *
	 * @return Grammar
	 */
	public static function fromExpression(Expression $expr, $start_rule=null)
    {
        if (!$start_rule) {
            $start_rule = $expr->name;
        }
		if (!$start_rule) {
			throw new GrammarException(
                'Top level expression must have a name.'
			);
        }
        $grammar = new static(); 
        $grammar[$start_rule] = $expr;
		return $grammar->unfold();
	}

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRules()
    {
        return $this->rules;
    }
    
    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function isFolded()
    {
        return $this->folded;
    }

    /**
     * {@inheritDoc}
     */
    public function fold($start_rule = null)
    {
        $traverser = (new GrammarTraverser(false, true))
            ->addVisitor(new GrammarVisitor)
        ;
        $traverser->traverse($this);

	   	if ($start_rule) {
			$this->setStartRule($start_rule);
		}

        $this->folded = true;

		return $this;
    }
    
    /**
     * {@inheritDoc}
     */
    public function unfold()
    {
        $traverser = (new GrammarTraverser(false, false))
            ->addVisitor(new GrammarVisitor)
        ;
        $traverser->traverse($this);

        $this->folded = false;

		return $this;
    }

    /**
     * {@inheritDoc}
     */
	public function finalize($start_rule = null)
	{
        $this->fold($start_rule);

		return $this;
	}

    /**
     * {@inheritDoc}
     */
    public function copy($deep=false)
    {
        $clone = clone $this;
        if ($deep) {
            $traverser = (new GrammarTraverser(true, $this->isFolded()))
                ->addVisitor(new GrammarVisitor)
            ;
            $traverser->traverse($clone);
        }

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function merge(GrammarInterface $other)
    {
        $new = $this->copy(true);
        $other = $other->copy(true);

        foreach ($other as $name => $rule) {
            $new[$name] = $rule;
        }
        
        return $new->unfold();
    }
    
    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        $out = '';
        if ($name = $this->getName()) {
            $out .= "%name $name\n";
        }
        $start_rule = $this->getStartRule();
        $out .= "%start {$this->default_rule}\n";

        $out .= "\n";
        foreach ($this->rules as $name => $expr) {
            $out .= sprintf("%s = %s\n", $name, $expr->asRhs());
        }

        return $out;
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

        $expr->name = $name;

        if (!$this->default_rule) {
            $this->default_rule = $name;
        }

        $this->rules[$name] = $expr;
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
