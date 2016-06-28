<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Visitor;

use ju1ius\Pegasus\NodeVisitor;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;


class MetaGrammarNodeVisitor extends NodeVisitor
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

	public function generic_visit(Node $node, $children)
	{
        if ($node instanceof Node\OneOf) {
            return $children[0];
        } elseif ($node instanceof Node\Not) {
            return null;
        } elseif ($node instanceof Node\Wrapper) {
            if (count($children) > 1) {
                throw new \LogicException('Wrapper nodes cannot have more than one children.');
            }
            return $children[0];
        } elseif ($node instanceof Node\Composite) {
            if (count($children) === 1) return $children[0];
            return $children ?: null;
        }
        if ($children) {
            throw new \LogicException('Shouldnt have reached here...');
        }
        return $node;
	}

    public function visit_grammar(Node\Sequence $node, $children)
    {
        list($directives, $rules) = $children;
        $grammar = new Grammar();
        // add rules
		foreach ($rules as $expr) {
			$grammar[$expr->name] = $expr;
        }
        // add directives
        if (isset($directives['name'])) {
            $grammar->setName($directives['name']);
        }
        if (isset($directives['start'])) {
            $grammar->setStartRule($directives['start']);
        }
        if (isset($directives['case_insensitive'])) {
            $grammar->setCaseInsensitive(true);
        }
        return $grammar;
    }

    // ----------- Directives

    public function visit_directives(Node\Quantifier $node, $children)
    {
        $directives = [];
        foreach ($children as list($name, $value)) {
            $directives[$name] = $value;   
        }
        return $directives;
    }

    public function visit_name_directive(Node\Sequence $node, $children)
    {
        list($dir, $name) = $children;
        return ['name', $name];
    }

    public function visit_start_directive(Node\Sequence $node, $children)
    {
        list($dir, $name) = $children;
        return ['start', $name];
    }

    public function visit_ci_directive(Node\Sequence $node, $children)
    {
        return ['case_insensitive', true];   
    }

    public function visit_ws_directive(Node\Sequence $node, $children)
    {
        list($dir, $expr) = $children;
        return ['whitespace' => $expr];
    }

    // ---------- Rules

	public function visit_rules(Node\Quantifier $node, $children)
    {
        return $children;
	}

	public function visit_rule(Node\Sequence $node, $children)
	{
		list($identifier, $expression) = $children;
		$expression->name = $identifier;
		return $expression;
	}
	
	public function visit_expression(Node\OneOf $node, $children)
	{
	    return $children[0];
	}

	public function visit_choice(Node\Sequence $node, $children)
	{
        list($alt1, $others) = $children;
        if (is_array($others)) {
            $alternatives = array_merge([$alt1], $others);
        } else {
            $alternatives = [$alt1, $others];
        }
        return new Expression\OneOf($alternatives);
	}

    public function visit_alternative(Node\OneOf $node, $children)
    {
        return $children[0];
    }
	
	public function visit_sequence(Node\Quantifier $node, $children)
	{
		return new Expression\Sequence($children);
	}

	public function visit_term(Node\OneOf $node, $children)
	{
	    return $children[0];
	}

	public function visit_labeled(Node\Sequence $node, $children)
	{
		list($label, $labelable) = $children;
		return new Expression\Label([$labelable], $label);
	}

	public function visit_prefixed(Node\OneOf $node, $children)
	{
	    return $children[0];
	}
	
	public function visit_lookahead(Node\Sequence $node, $children)
	{
		list($amp, $prefixable) = $children;
		return new Expression\Lookahead([$prefixable]);
	}

	public function visit_not(Node\Sequence $node, $children)
	{
		list($bang, $prefixable) = $children;
		return new Expression\Not([$prefixable]);
	}

    public function visit_skip(Node\Sequence $node, $children)
    {
        list($tilde, $prefixable) = $children;
        return new Expression\Skip([$prefixable]);
    }

	public function visit_prefixable(Node\OneOf $node, $children)
	{
		return $children[0];
	}

	public function visit_suffixed(Node\Sequence $node, $children)
	{
		list($suffixable, $quantifier) = $children;
		if (!empty($quantifier->matches[1])) {
            $class = self::$QUANTIFIER_CLASSES[$quantifier->matches[1]];
            return new $class([$suffixable]);
		}
        $min = (int) $quantifier->matches[2];
		$max = !empty($quantifier->matches[3]) ? (int) $quantifier->matches[3] : INF;
        return new Expression\Quantifier([$suffixable], $min, $max);
	}

	public function visit_labelable(Node\OneOf $node, $children)
	{
		return $children[0];
	}

	public function visit_primary(Node\OneOf $node, $children)
	{
	    return $children[0];
	}

	public function visit_parenthesized(Node\Sequence $node, $children)
	{
		list($lp, $expr, $rp) = $children;
		return $expr;
	}

	public function visit_atom(Node\OneOf $node, $children)
	{
		return $children[0];
	}

    public function visit_eof(Node\Sequence $node, $children)
    {
        return new Expression\EOF();
    }
    
    public function visit_epsilon(Node\Sequence $node, $children)
    {
        return new Expression\Epsilon();
    }
    
	public function visit_quantifier(Node\Sequence $node, $children)
    {
        // quantifier is handled in visit_suffixed
	    return $children[0];
	}

	public function visit_literal(Node\Sequence $node, $children)
	{
		$quote = $children[0]->matches[1];
		$str = $children[0]->matches[2];
		return new Expression\Literal($str, '', $quote);
	}

	public function visit_regex(Node\Sequence $node, $children)
	{
		$regex = $children[0];
		list($match, $pattern, $flags) = $regex->matches;
		return new Expression\Regex($pattern, '', str_split($flags));
	}
	
	public function visit_reference(Node\Sequence $node, $children)
	{
		return new Expression\Reference($children[0]);
	}

    public function visit_label(Node\Regex $node, $children)
    {
        return $node->matches[1];   
    }

	public function visit_identifier(Node\Sequence $node, $children)
	{
		return $children[0]->matches[0];
	}
}
