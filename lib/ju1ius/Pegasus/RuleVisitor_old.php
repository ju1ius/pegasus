<?php

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Exception\UndefinedLabelException;
use ju1ius\Pegasus\Exception\VisitationError;
use ju1ius\Pegasus\Node\Composite;


/**
 * Class RuleVisitor
 * @author ju1ius
 */
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
            'ignore' => ['_', 'equals']
        ]);
    }
    
    /**
     * Documentation for genericVisit
     *
     * @param $node, $visited_children
     * @return void
     */
    public function generic_visit($node, $visited_children)
    {
        return $visited_children ?: $node;
        return $node instanceof Composite
            ? $visited_children ?: null
            : $node;
    }

    /**
     * Collate all the rules into a map.
     *
     * The default rule is the first one.
     * Or, if you have more than one rule of that name,
     * it's the last-occurring rule of that name.
     * (This lets you override the default rule when you extend a grammar.)
     *
     * @param $node, $visited_children
     * @return array [$rule_map, $defaut_rule]
     */
    public function visit_rules($node, $visited_children)
    {
        list($rules) = $visited_children;
        // Map each rule's name to its Expression.
        // Later rules of the same name override earlier ones.
        // This lets us define rules multiple times and have the
        // last declarations win, so you can extend grammars by concatenation.
        $rule_map = [];
        foreach ($rules as $expr) {
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

    public function visit_rule($node, $visited_children)
    {
        list($identifier, $expr) = $visited_children;
        $expr->name = $identifier;
        return $expr;
    }
    
    /**
     * Documentation for visit_lookahead_term
     *
     * @param $lookahead_term, $visited_children
     * @return void
     */
    public function visit_lookahead_term($node, $visited_children)
    {
        list($ampersand, $term) = $visited_children;
        return new Expression\Lookahead([$term]);
    }

    /**
     * Documentation for visit_not_term
     *
     * @param $not_term, $visited_children
     * @return void
     */
    public function visit_not_term($node, $visited_children)
    {
        list($excl_mark, $term) = $visited_children;
        return new Expression\Not([$term]);
    }

    public function visit_expression($node, $visited_children)
    {
        return $this->liftChild($node, $visited_children);
    }

    public function visit_term($node, $visited_children)
    {
        return $this->liftChild($node, $visited_children);
    }

    /**
     * A parsed Sequence looks like [$term node, OneOrMore node of $another_term].
     * Flatten it out.
     *
     * @param $reference, $visited_children
     * @return void
     */
    public function visit_sequence($node, $visited_children)
    {
        //\Psy\Shell::debug(get_defined_vars());
        return new Expression\Sequence($visited_children);
        //list($term, $other_terms) = $visited_children;
        //return new Expression\Sequence(array_merge([$term], $other_terms));
    }

    /**
     * Documentation for visit_ored
     *
     * @param $ored, $visited_children
     * @return void
     */
    public function visit_ored($ored, $visited_children)
    {
        list($term, $other_terms) = $visited_children;
        return new Expression\OneOf(array_merge([$term], $other_terms));
    }
    
    /**
     * Return just the term from an $or_term.
     * We already know it's going to be ored, from the containing $ored
     *
     * @param $or_term, $visited_children
     * @return void
     */
    public function visit_or_term($or_term, $visited_children)
    {
        list($pipe, $terms) = $visited_children;
        return $terms;
    }

    public function visit_atom($node, $visited_children)
    {
        list($atom) = $visited_children;
        return $atom;
    }
    
    /**
     * Treat a parenthesized subexpression as just its contents.
     *
     * Its position in the tree suffices to maintain its grouping semantics.
     * $visited_children = ['(', _1, $expr, ')', _2]
     */
    public function visit_parenthesized($node, $visited_children)
    {
        list($lparen, $expr, $rparen) = $visited_children;
        return $expr;
    }
    
    /**
     * Documentation for visit_quantified
     *
     * @param $quantified, $visited_children
     * @return void
     */
    public function visit_quantified($node, $visited_children)
    {
        list($atom, $quantifier) = $visited_children;
        if ($quantifier->matches[1]) {
            $class = self::$QUANTIFIER_CLASSES[$quantifier->matches[1]];
            return new $class([$atom]);
        }
        $min = (int) $quantifier->matches[2];
        $max = $quantifier->matches[3] ? (int) $quantifier->matches[3] : null;
        return new Expression\Quantifier([$atom], '', $min, $max);
    }

    /**
     * Turn a quantifier into just its symbol-matching node.
     * 
     * @param $visited_children [symbol, _]
     * @return Expression
     **/
    public function visit_quantifier($node, $visited_children)
    {
        list($regex) = $visited_children;
        return $regex;
    }

    /**
     * Documentation for visit_regex
     *
     * @param $regex, $visited_children
     * @return void
     */
    public function visit_regex($node, $visited_children)
    {
        list($regex) = $visited_children;
        $pattern = $regex->matches[1];
        $flags = str_split($regex->matches[2]);
        return new Expression\Regex($pattern, '', $flags);
    }

    /**
     * Documentation for visit_literal
     *
     * @todo escape characters & quotes
     *
     * @param $literal, $visited_children
     * @return void
     */
    public function visit_literal($literal, $visited_children)
    {
        list($regex) = $visited_children;
        $quote_char = $regex->matches[1];
        $str = $regex->matches[2];
        return new Expression\Literal($str);
    }

    /**
     * Documentation for visit_reference
     *
     * @param $reference, $visited_children
     * @return void
     */
    public function visit_reference($reference, $visited_children)
    {
        list($identifier, $not_equals) = $visited_children;
        //list($identifier) = $visited_children;
        return new Expression\LazyReference($identifier);
    }

    /**
     * Documentation for visit_label
     *
     * @param $label, $visited_children
     * @return void
     */
    public function visit_identifier($node, $visited_children)
    {
        list($name) = $visited_children;
        return $name->matches[0];
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
        if ($expr instanceof Expression\LazyReference) {
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
        $members = $expr instanceof Expression\Composite ? $expr->members : [];
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
