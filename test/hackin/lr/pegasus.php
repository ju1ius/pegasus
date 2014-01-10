<?php
require_once __DIR__.'/../../../vendor/autoload.php';

use ju1ius\Pegasus\Exception\UndefinedLabelException;

use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Regex;
use ju1ius\Pegasus\Expression\Optional;
use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\ZeroOrMore;
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\Lookahead;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\LazyReference;
use ju1ius\Pegasus\Parser;
use ju1ius\Pegasus\NodeVisitor;


$syntax = <<<'EOS'
grammar = _ rules
	
rules = rule+

rule = identifier equals expression

expression	= choice | sequence

choice		= expression '|' terms 
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

primary		= '(' expression ')'
			| atom
			
atom		= literal | regex | reference

equals		= / \s* = \s* /

reference	= / (?>([a-zA-Z]\w*)) (?>\s*) (?>(?!=)) /

quantifier  = / ([*+?]) | (?: \{ (\d+)(?:,(\d*))? \} ) / _

regex		= / \/ ((?: (?:\\\\.) | [^\/] )*) \/ ([ilmsux]*)? /

literal		= / (["\']) ((?: (?:\\.) | (?:(?!\1).) )*) \1 /

label		= / ([a-zA-Z]\w*): /

identifier	= / [a-zA-Z]\w* /

_			= (ws | comment)*

comment		= / \# ([^\r\n]*) /

ws			= /\s+/
EOS;


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
$quantifier = new Sequence([
	new Regex('(?> ([*+?]) | (?: \{ (\d+) (?:,(\d*))?\} ) )', 'quantifier_rx'),
	$_
], 'quantifier');
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
$suffixed->members = [$suffixable, $quantifier, $_];
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


class MyVisitor extends NodeVisitor
{
    static protected $QUANTIFIER_CLASSES = [
        '?' => 'ju1ius\Pegasus\Expression\Optional',
        '*' => 'ju1ius\Pegasus\Expression\ZeroOrMore',
        '+' => 'ju1ius\Pegasus\Expression\OneOrMore'
    ];

    public function __construct()
    {
        parent::__construct([
            'ignore' => ['_', 'OR', 'equals']
        ]);
    }

	public function generic_visit($node, $children)
	{
		$num_children = count($children);
		return $num_children > 1
			? $children
			: $num_children === 1 ? $children[0] : $node
		;	
	}

	public function visit_identifier($node, $children)
	{
		return $children[0]->matches[0];
	}

	public function visit_literal($node, $children)
	{
		$quote = $children[0]->matches[1];
		$str = $children[0]->matches[2];
		return new Literal($str);
	}

	public function visit_regex($node, $children)
	{
		$regex = $children[0];
		list($match, $pattern, $flags) = $regex->matches;
		return new Regex($pattern, '', str_split($flags));
	}
	
	public function visit_reference($node, $children)
	{
		return new LazyReference($node->matches[1]);
	}

	public function visit_atom($node, $children)
	{
		return $children[0];
	}

	public function visit_primary($node, $children)
	{
	    return $children[0];
	}

	public function visit_parenthesized($node, $children)
	{
		list($lp, $expr, $rp) = $children;
		return $expr;
	}
	
	public function visit_expression($node, $children)
	{
	    return $children[0];
	}

	public function visit_rule($node, $children)
	{
		list($identifier, $expression) = $children;
		$expression->name = $identifier;
		return $expression;
	}

	public function visit_term($node, $children)
	{
	    return $children[0];
	}

	public function visit_labeled($node, $children)
	{
		list($label, $labelable) = $children;
		$labelable->label = $label;
		return $labelable;
	}

	public function visit_labelable($node, $children)
	{
		return $children[0];
	}

	public function visit_prefixed($node, $children)
	{
	    return $children[0];
	}

	public function visit_prefixable($node, $children)
	{
		return $children[0];
	}
	
	public function visit_not($node, $children)
	{
		list($bang, $prefixable) = $children;
		return new Not([$prefixable]);
	}

	public function visit_lookahead($node, $children)
	{
		list($amp, $prefixable) = $children;
		return new Lookahead([$prefixable]);
	}

	public function visit_suffixed($node, $children)
	{
		list($suffixable, $quantifier) = $children;
		if (!empty($quantifier->matches[1])) {
            $class = self::$QUANTIFIER_CLASSES[$quantifier->matches[1]];
            return new $class([$suffixable]);
		}
        $min = (int) $quantifier->matches[2];
		$max = isset($quantifier->matches[3]) ? (int) $quantifier->matches[3] : null;
        return new Quantifier([$suffixable], '', $min, $max);
	}
	
	public function visit_sequence($node, $children)
	{
		list($terms, $term) = $children;
		return new Sequence(array_merge([$terms], [$term]));
		return $node;
	}

	public function visit_choice($node, $children)
	{
		list($expr, $terms) = $children;
		return new OneOf([$expr, $terms]);
	}

	public function visit_rules($node, $children)
	{
		$rules = $children;
		$rule_map = [];
		foreach ($children as $expr) {
			$rule_map[$expr->name] = $expr;
		}
        // Resolve references. This tolerates forward references.
        $names = array_keys($rule_map);
        $unwalked_names = array_combine($names, $names);
        while (list($rule_name,) = each($unwalked_names)) {
            $rule_map[$rule_name] = $this->_resolveRefs($rule_map, $rule_map[$rule_name], $unwalked_names, [$rule_name]);
            unset($unwalked_names[$rule_name]);
        }
        $default = $rules[0] instanceof Expression\LazyReference
            ? $rule_map[$rules[0]->identifier]
            : $rules[0]
        ;
        return [$rule_map, $default];
	}

    /**
     * Return an expression with all its lazy references recursively resolved.
     *
     * Resolve any lazy references in the expression ``expr``,
     * recursing into all subexpressions.
     * Populate $rule_map with any other rules (named expressions)
     * resolved along the way.
     * Remove from $unwalked_names any which were resolved.
     *
     * @param $walking_names: The stack of labels we are currently recursing through.
     * This prevents infinite recursion for circular refs.
     *
     * @param $rule_map, $expr, $unwalked_names, $walking_names
     * @return void
     */
    protected function _resolveRefs(array $rule_map, $expr, array &$unwalked_names, array $walking_names)
    {
        // If it's a top-level (named) expression and we've already walked it,
        // don't walk it again:
        if ($expr->name && !isset($unwalked_names[$expr->name])) {
            // $unwalked_names started out with all the rule names in it, so,
            // if this is a named expr and it isn't in there,
            // it must have been resolved.
            return $rule_map[$expr->name];
        }
        // If not, resolve it:
        if ($expr instanceof LazyReference) {
            //$label = (string) $expr;
            $label = $expr->identifier;
            if (!isset($walking_names[$label])) {
                // We aren't already working on traversing this label:
                if (!isset($rule_map[$label])) {
                    throw new UndefinedLabelException($label);
                }
                $reffed_expr = $rule_map[$label];
                $walking_names[] = $label;
                $rule_map[$label] = $this->_resolveRefs(
                    $rule_map,
                    $reffed_expr,
                    $unwalked_names,
                    $walking_names 
                );
                // If we recurse into a compound expression, removal happens in there.
                // But if this label points to a non-compound expression,
                // like a literal or a regex or another lazy reference,
                // we need to do this here:
                unset($unwalked_names[$label]);
            }
            return $rule_map[$label];
        }
        $members = $expr instanceof Composite ? $expr->members : [];
        if ($members) {
            $expr->members = [];
            foreach ($members as $member) {
                $expr->members[] = $this->_resolveRefs($rule_map, $member, $unwalked_names, $walking_names);
            }
        }
        if ($expr->name) {
            unset($unwalked_names[$expr->name]);
        }
        return $expr;
    }
}


$parser = new Parser\LRPackrat($grammar);

//$tree = $parser->parse(<<<'EOS'
//hello = rule ("other" | &rule)
//rule = oneofus+ | !hello
//oneofus = "you"{1} | "me"?
//EOS
//);
$tree = $parser->parse($syntax);
$visitor = new MyVisitor([
	'ignore' => ['_', 'OR', 'equals']
]);
list($rules, $start) = $visitor->visit($tree);
foreach ($rules as $name => $rule) {
	if (!method_exists($rule, 'asRule')) {
		var_dump($rule);
	}
	echo $rule->asRule(), "\n";
}

//var_dump($new_tree);
