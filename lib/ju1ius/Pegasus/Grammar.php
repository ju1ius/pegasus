<?php

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Exception\GrammarException;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Parser\LRPackrat as Parser;
use ju1ius\Pegasus\Visitor\RuleVisitor;
use ju1ius\Pegasus\Visitor\ExpressionTraverser;
use ju1ius\Pegasus\Visitor\ReferenceResolver;
use ju1ius\Pegasus\Visitor\RuleCollector;


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
 * $parse_tree = (new Parser($grammar))->parse('Hello, my good sir');
 * </code>
 *
 * Or start parsing from any of the other expressions.
 * You can pull them out of the grammar as if it were an associative array:
 *
 * <code>
 * $parse_tree = (new Parser($grammar['greeting']))->parse('Hi');
 * </code>
 *
 * You can also just construct a bunch of Expression objects yourself
 * and stitch them together into a language by using:
 * <code>
 * Grammar::fromExpression($my_expression);
 * </code>
 * But using a Grammar has some important advantages:
 *
 * - Languages are much easier to define in the nice syntax it provides.
 * - Circular references aren't a pain.
 * - It does all kinds of whizzy space- and time-saving optimizations, like
 *   factoring up repeated subexpressions into a single object,
 *   which should increase cache hit ratio.
 */
class Grammar extends AbstractGrammar
{
	/**
	 * Protected Grammar constructor.
	 *
	 * You must use Grammar::fromSyntax or Grammar::fromExpression
	 * to build Grammar objects.
	 *
	 * @param Expression[]	$rules		An array of ['rule_name' => $expression].
	 * @param Expression	$start_rule	The top level expression of this grammar.
	 **/
	public function __construct(array $rules=[], Expression $start_rule=null)
	{
		$this->rules = $rules;
		$this->default_rule = $start_rule;
	}

	/**
	 * Factory method that constructs a Grammar object from a syntax string.
	 *
	 * @param string	$syntax
	 *
	 * @return Grammar
	 */
	public static function fromSyntax($syntax)
	{
		$metagrammar = MetaGrammar::create();
		$tree = (new Parser($metagrammar))->parse($syntax);
		list($rules, $start) = (new RuleVisitor)->visit($tree);

		$grammar = new static($rules, $start);
		$grammar->resolveReferences();
		return $grammar;
	}

	/**
	 * Factory method that constructs a Grammar object from an Expression.
	 *
	 * @param Expression $expr
	 *
	 * @return Grammar
	 */
	public static function fromExpression(Expression $expr)
	{
		if (!$expr->name) {
			throw new GrammarException(
				'Top level expression must have a name.'
			);
		}
		$grammar = new static();
		$traverser = (new ExpressionTraverser)
			->addVisitor(new RuleCollector($grammar))
			->addVisitor(new ReferenceResolver($grammar))
		;
		$expr = $traverser->traverse($expr);
		$grammar->setStartRule($expr->name);

		return $grammar;
	}
}
