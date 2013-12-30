<?php
require_once __DIR__.'/../vendor/autoload.php';

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Packrat\LRParser;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\NodeVisitor;


class Calculator extends NodeVisitor
{
    /**
     * Unnamed Node (not a rule reference)
     * discard it and return it's children, if any
     */
    public function generic_visit($node, $children)
    {
        return $children ?: $node;
    }

    public function visit_expression($node, $children)
    {
        list($first_term, $_, $other_terms) = $children;
        if (!is_array($other_terms)) {
            return $first_term;
        }
        foreach ($other_terms as $seq) {
            list($plus_or_minus, $_, $term, $_) = $seq;
            $operator = (string) $plus_or_minus[0];
            if ('+' === $operator) {
                $first_term += $term;
            } else {
                $first_term -= $term;
            }
        }
        return $first_term;
    }

    public function visit_term($node, $children)
    {
        list($first_factor, $_, $other_factors) = $children;
        if (!is_array($other_factors)) {
            return $first_factor;
        }
        foreach ($other_factors as $seq) {
            list($mul_or_div, $_, $factor, $_) = $seq;
            $operator = (string) $mul_or_div[0];
            if ('*' === $operator) {
                $first_factor *= $factor;
            } else {
                $first_factor /= $factor;
            }
        }
        return $first_factor;
    }

    public function visit_factor($node, $children)
    {
        return $children[0];
    }

    public function visit_parenthesized($node, $children)
    {
        list($lparen, $_1, $expression, $_2, $rparen) = $children;
        return $expression;
    }

    public function visit_primary($node, $children)
    {
        echo $node->treeview(), "\n";
    }
    

    public function visit_number($node, $children)
    {
        return $children[0];
    }

    public function visit_int($node, $children)
    {
        return (int) $node->match[0];
    }

    public function visit_float($node, $children)
    {
        return (float) $node->match[0];
    }
}

$syntax = <<<'EOS'
expr = (expr "+" term)
        | (expr "-" term)
        | term

term = (term "*" primary)
        | (term "/" primary)
        | primary

primary = ("(" expr ")")
        | number

number = float | int
float = /-?[0-9]*\.[0-9]+/
int = /-?[0-9]+/
EOS;

$grammar = new Grammar($syntax);
$parser = new LRParser($grammar);
$tree = $parser->parse($argv[1]);
$calculator = new Calculator();
$result = $calculator->visit($tree);
echo "Result: ", $result, "\n";
