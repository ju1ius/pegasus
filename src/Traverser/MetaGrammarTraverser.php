<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Traverser;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Assert;
use ju1ius\Pegasus\Expression\EOF;
use ju1ius\Pegasus\Expression\Epsilon;
use ju1ius\Pegasus\Expression\Label;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Expression\RegExp;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\Skip;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Node;

class MetaGrammarTraverser extends DepthFirstNodeTraverser
{
    static protected $QUANTIFIER_CLASSES = [
        '?' => 'ju1ius\Pegasus\Expression\Optional',
        '*' => 'ju1ius\Pegasus\Expression\ZeroOrMore',
        '+' => 'ju1ius\Pegasus\Expression\OneOrMore',
    ];

    public function __construct()
    {
        parent::__construct([
            'ignore' => ['_', 'OR', 'equals', 'arrow_left'],
        ]);
    }

    public function genericVisit(Node $node, $children)
    {
        if ($node->isTransient) {
            // skip transient nodes (shouldn't happen)
            return null;
        }
        $numChildren = count($children);
        if ($numChildren === 1) {
            return $children[0];
        }
        if ($numChildren) {
            return $children;
        }
        if ($children) {
            throw new \LogicException('Shouldnt have reached here...');
        }

        return $node;
    }

    public function visit_grammar(Node $node, $directives, $rules)
    {
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

    //
    // Directives
    // --------------------------------------------------------------------------------------------------------------

    public function visit_directives(Node $node, ...$children)
    {
        $directives = [];
        foreach ($children as list($name, $value)) {
            $directives[$name] = $value;
        }

        return $directives;
    }

    public function visit_name_directive(Node $node, $dir, $name)
    {
        return ['name', $name];
    }

    public function visit_start_directive(Node $node, $dir, $name)
    {
        return ['start', $name];
    }

    public function visit_ci_directive(Node $node, ...$children)
    {
        return ['case_insensitive', true];
    }

    public function visit_ws_directive(Node $node, $dir, Expression $expr)
    {
        return ['whitespace' => $expr];
    }

    //
    // Rules
    // --------------------------------------------------------------------------------------------------------------

    public function visit_rules(Node $node, Expression ...$children)
    {
        return $children;
    }

    public function visit_rule(Node $node, $identifier, Expression $expression)
    {
        $expression->name = $identifier;

        return $expression;
    }

    //
    // Composite Expressions
    // --------------------------------------------------------------------------------------------------------------

    public function visit_choice(Node $node, Expression $alt1, $others)
    {
        if (is_array($others)) {
            $alternatives = array_merge([$alt1], $others);
        } else {
            $alternatives = [$alt1, $others];
        }

        return new OneOf($alternatives);
    }

    public function visit_sequence(Node $node, Expression ...$children)
    {
        return new Sequence($children);
    }

    //
    // Decorator Expressions
    // --------------------------------------------------------------------------------------------------------------

    public function visit_labeled(Node $node, $label, Expression $labelable)
    {
        return new Label([$labelable], $label);
    }

    public function visit_assert(Node $node, $amp, Expression $prefixable)
    {
        return new Assert([$prefixable]);
    }

    public function visit_not(Node $node, $bang, Expression $prefixable)
    {
        return new Not([$prefixable]);
    }

    public function visit_skip(Node $node, $tilde, Expression $prefixable)
    {
        return new Skip([$prefixable]);
    }

    public function visit_quantifier(Node $node, $quantifier)
    {
        $matches = $quantifier['matches'];
        if (!empty($matches[1])) {
            $class = self::$QUANTIFIER_CLASSES[$matches[1]];

            return new $class([]);
        }
        $min = (int)$matches[2];
        $max = !empty($matches[3]) ? (int)$matches[3] : INF;

        return new Quantifier([], $min, $max);
    }

    public function visit_suffixed(Node $node, $suffixable, Quantifier $suffix)
    {
        $suffix[0] = $suffixable;

        return $suffix;
    }

    //
    // Terminal Expressions
    // --------------------------------------------------------------------------------------------------------------

    public function visit_literal(Node $node, $literal)
    {
        $quoteChar = $literal['matches'][1];
        $str = $literal['matches'][2];

        return new Literal($str, '', $quoteChar);
    }

    public function visit_regex(Node $node, $regexp)
    {
        list($match, $pattern, $flags) = $regexp['matches'];

        return new RegExp($pattern, str_split($flags));
    }

    public function visit_reference(Node $node, $identifier)
    {
        return new Reference($identifier);
    }

    public function visit_eof(Node $node, ...$children)
    {
        return new EOF();
    }

    public function visit_epsilon(Node $node, ...$children)
    {
        return new Epsilon();
    }

    //
    // Expression parts
    // --------------------------------------------------------------------------------------------------------------

    public function visit_expression(Node $node, $expr)
    {
        return $expr;
    }

    public function visit_alternative(Node $node, $choice)
    {
        return $choice;
    }

    public function visit_term(Node $node, $term)
    {
        return $term;
    }

    public function visit_prefixed(Node $node, $prefixed)
    {
        return $prefixed;
    }

    public function visit_prefixable(Node $node, $prefixable)
    {
        return $prefixable;
    }

    public function visit_labelable(Node $node, $labelable)
    {
        return $labelable;
    }

    public function visit_primary(Node $node, $primary)
    {
        return $primary;
    }

    public function visit_parenthesized(Node $node, $lp, $expr, $rp)
    {
        return $expr;
    }

    public function visit_atom(Node $node, $atom)
    {
        return $atom;
    }

    public function visit_label(Node $node, $label)
    {
        return $label['matches'][1];
    }

    public function visit_identifier(Node $node, $identifier)
    {
        return $identifier['matches'][0];
    }
}
