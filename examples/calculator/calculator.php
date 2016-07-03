<?php

require_once __DIR__.'/../vendor/autoload.php';

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Parser\Packrat as Parser;
use ju1ius\Pegasus\Traverser\DepthFirstNodeTraverser;


class Calculator extends DepthFirstNodeTraverser
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

    public function visit_number($node, $children)
    {
        return $children[0];
    }

    public function visit_int($node, $children)
    {
        return (int) $node->matches[0];
    }

    public function visit_float($node, $children)
    {
        return (float) $node->matches[0];
    }
}


$syntax = <<<'EOS'
# grammar: Calculator

expression = term _ (('+'|'-') _ term _)*
term = factor _ (('*'|'/') _ factor _)*
factor = number | parenthesized
parenthesized = '(' _ expression _ ')'
number = float | int
float = /-?[0-9]*\.[0-9]+/
int = /-?[0-9]+/
_ = /\s*/
EOS;

$grammar = new Grammar($syntax);
$parser = new Parser($grammar);
$tree = $parser->parse($argv[1]);
$calculator = new Calculator();
$result = $calculator->traverse($tree);
echo "Result: ", $result, "\n";
echo "Mem peak: ", memory_get_peak_usage(), "\n";
