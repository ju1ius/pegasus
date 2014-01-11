<?php

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Regex;
use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\ZeroOrMore;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Sequence;

use ju1ius\Pegasus\Parser\LRPackrat as Parser;
use ju1ius\Pegasus\Visitor\RuleVisitor;


/**
 * Factory class that builds a Grammar instance
 * capable of parsing other grammars.
 *
 */
class MetaGrammar extends AbstractGrammar
{
	const SYNTAX = <<<'EOS'

grammar = _ rules
	
rules = rule+

rule = identifier equals expression

expression	= choice | sequence

choice		= expression OR terms 
			| terms
			
terms		= sequence | term

sequence	= terms term

term		= labeled | labelable

labeled		= label labelable

labelable	= prefixed | prefixable

prefixed	= lookahead | not 

lookahead	= '&' prefixable

not			= '!' prefixable

prefixable	= prefixed | suffixable | primary

suffixable	= suffixed | primary

suffixed	= suffixable | quantifier

primary		= '(' _ expression _ ')' _
			| atom
			
atom		= literal | regex | reference

equals		= / \s* = \s* /

reference	= / (?>([a-zA-Z_]\w*)) (?>\s*) (?>(?!=)) /

quantifier  = / ([*+?]) | (?: \{ (\d+)(?:,(\d*))? \} ) / _

regex		= / \/ ((?: (?:\\\\.) | [^\/] )*) \/ ([ilmsux]*)? / _

literal		= / (["\']) ((?: (?:\\.) | (?:(?!\1).) )*) \1 / _

label		= / ([a-zA-Z_]\w*): /

identifier	= / [a-zA-Z_]\w* / _

OR          = / \s* \| \s* /

_			= (ws | comment)*

comment		= / \# ([^\r\n]*) /

ws			= /\s+/

EOS;

	/**
	 * @var Grammar The unique instance of the meta grammar.
	 */
	private static $instance = null;

	/**
	 * Private constructor.
	 *
	 * You can't instanciate MetaGrammar.
	 * You just call MetaGrammar::create() and it returns an unique instance of Grammar.
	 */
	private function __construct(){}

	/**
	 * Factory method for MetaGrammar.
	 *
	 * @return MetaGrammar
	 */
	public static function create()
	{
		if (null === self::$instance) {
			$expr = self::buildExpression();
			$grammar = Grammar::fromExpression($expr);
			$parser = new Parser($grammar);
			$tree = $parser->parse(self::SYNTAX);
			list($rules, $default) = (new RuleVisitor)->visit($tree);

			self::$instance = new Grammar($rules, $default);
			self::$instance->resolveReferences();
		}

		return self::$instance;
	}

	private static function buildExpression()
	{
		$ws = new Regex('\s+', 'ws');
		$ws_ = new Regex('\s*', 'ws_');
		$comment = new Regex('\#([^\n]*)', 'comment');
		$ws_com = new OneOf([$ws, $comment]);
		$_ = new ZeroOrMore([$ws_com], '_');
		$ident = new Regex('[a-zA-Z_]\w*', 'ident');
		$label = new Regex('([a-zA-Z_]\w*):', 'label');
		$identifier = new Sequence([
			new Regex('[a-zA-Z_]\w*'),
			$_
		], 'identifier');
		$literal = new Sequence([
			new Regex('(["\'])((?:(?:\\\\.)|(?:(?!\1).))*)\1', 'literal_rx'),
			$_
		], 'literal');
		$regex = new Sequence([
			new Regex('\/((?:(?:\\\\.)|[^\/])*)\/([ilmsux]*)?', 'regex_rx'),
			$_
		], 'regex');
		// the two following regexes must have atomic group in order to match properly
		$quantifier = new Sequence([
			new Regex('(?> ([*+?]) | (?: \{ (\d+) (?:,(\d*))?\} ) )', 'quantifier_rx'),
			$_
		], 'quantifier');
		// TODO: see if this is really a performance improvement
		// compared to adding a Not expression
		$reference = new Regex('(?>([a-zA-Z_]\w*))(?>\s*)(?>(?!=))', 'reference');
		$equals = new Regex('\s*=\s*', 'equals');
		$OR = new Regex('\s*\|\s*', 'OR');

		$atom = new OneOf([
			//new Literal ('EOF', 'eof'),
			//new Literal ('E', 'epsilon'),
			$literal,
			$regex,
			$reference
		], 'atom');

		$expression = new OneOf([], 'expression');
		$primary = new OneOf([
			new Sequence([
				new Literal('('),
				$_,
				$expression,
				$_,
				new Literal(')'),
				$_
			], 'parenthesized'),
			$atom
		]);

		$suffixed = new Sequence([], 'suffixed');
		$suffixable = new OneOf([], 'suffixable');
		$suffixed->members = [$suffixable, $quantifier];
		$suffixable->members = [$suffixed, $primary];

		$prefixed = new OneOf([], 'prefixed');
		$prefixable = new OneOf([$prefixed, $suffixable, $primary], 'prefixable');
		$prefixed->members = [
			new Sequence([
				new Literal('!'), $prefixable
			], 'not'),
			new Sequence([
				new Literal('&'), $prefixable
			], 'lookahead')
		];

		$labelable = new OneOf([$prefixed, $prefixable], 'labelable');
		$labeled = new Sequence([$label, $labelable, $_], 'labeled');
		$term = new OneOf([$labeled, $labelable], 'term');
		$terms = new OneOf([], 'terms');
		$terms->members = [
			new Sequence([$terms, $term], 'sequence'),
			$term
		];
		$expression->members = [
			new Sequence([$expression, $OR, $terms], 'choice'),
			$terms
		];
		$rule = new Sequence([
			$identifier, $equals, $expression
		], 'rule');
		$rules = new OneOrMore([$rule], 'rules');
		$grammar = new Sequence([
			$_, $rules
		], 'grammar');

		return $grammar;
	}
}
