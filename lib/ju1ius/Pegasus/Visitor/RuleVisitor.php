<?php

namespace ju1ius\Pegasus\Visitor;

use ju1ius\Pegasus\NodeVisitor;
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
use ju1ius\Pegasus\Expression\Reference;


class RuleVisitor extends NodeVisitor
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

	public function visit_rules($node, $children)
	{
		$rules = $children;
		$rule_map = [];
		foreach ($children as $expr) {
			$rule_map[$expr->name] = $expr;
		}
        $default = $rules[0] instanceof Reference
            ? $rule_map[$rules[0]->identifier]
            : $rules[0]
        ;
		return [$rule_map, $rules[0]->name];
		return [$rule_map, $default->name];
	}

	public function visit_rule($node, $children)
	{
		list($identifier, $expression) = $children;
		$expression->name = $identifier;
		return $expression;
	}
	
	public function visit_expression($node, $children)
	{
	    return $children[0];
	}

	public function visit_choice($node, $children)
	{
		if (count($children) === 1) {
			//var_dump($children);
		}
		list($expr, $terms) = $children;
		return new OneOf([$expr, $terms]);
	}
	
	public function visit_sequence($node, $children)
	{
		list($terms, $term) = $children;
		return new Sequence(array_merge([$terms], [$term]));
		return $node;
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

	public function visit_prefixed($node, $children)
	{
	    return $children[0];
	}
	
	public function visit_lookahead($node, $children)
	{
		list($amp, $prefixable) = $children;
		return new Lookahead([$prefixable]);
	}

	public function visit_not($node, $children)
	{
		list($bang, $prefixable) = $children;
		return new Not([$prefixable]);
	}

	public function visit_prefixable($node, $children)
	{
		return $children[0];
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

	public function visit_labelable($node, $children)
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

	public function visit_atom($node, $children)
	{
		return $children[0];
	}

	public function visit_quantifier($node, $children)
	{
	    return $children[0];
	}

	public function visit_literal($node, $children)
	{
		$quote = $children[0]->matches[1];
		$str = $children[0]->matches[2];
		return new Literal($str, '', $quote);
	}

	public function visit_regex($node, $children)
	{
		$regex = $children[0];
		list($match, $pattern, $flags) = $regex->matches;
		return new Regex($pattern, '', str_split($flags));
	}
	
	public function visit_reference($node, $children)
	{
		return new Reference($children[0]);
	}

	public function visit_identifier($node, $children)
	{
		return $children[0]->matches[0];
	}
}
