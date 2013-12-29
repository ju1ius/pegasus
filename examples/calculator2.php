<?php

require_once __DIR__.'/../vendor/autoload.php';

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\NodeVisitor;


class Calculator extends NodeVisitor
{
    public function __construct($actions=[], $precision=6)
    {
        parent::__construct($actions);
        $this->scale = $precision;
    }
    
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
        list($first_term, $other_terms) = $children;
        if (!is_array($other_terms)) {
            return $first_term;
        }
        foreach ($other_terms as $seq) {
            list($plus_or_minus, $term) = $seq;
            $operator = (string) $plus_or_minus[0];
            if ('+' === $operator) {
                $first_term = bcadd($first_term, $term, $this->scale);
            } else {
                $first_term = bcsub($first_term, $term, $this->scale);
            }
        }
        return $first_term;
    }
    public function visit_term($node, $children)
    {
        list($first_factor, $other_factors) = $children;
        if (!is_array($other_factors)) {
            return $first_factor;
        }
        foreach ($other_factors as $seq) {
            list($mul_or_div, $factor) = $seq;
            $operator = (string) $mul_or_div[0];
            if ('*' === $operator) {
                $first_factor = bcmul($first_factor, $factor, $this->scale);
            } else {
                $first_factor = bcdiv($first_factor, $factor, $this->scale);
            }
        }
        return $first_factor;
    }
}


$syntax = <<<'EOS'
# grammar: Calculator

expression = term _ (('+'|'-') _ term)*
term = factor _ (('*'|'/') _ factor)*
factor = number | parenthesized
parenthesized = LPAREN expression RPAREN
number = expo | float | int
expo = (float | int) 'e' int
float = /-?[0-9]*\.[0-9]+/
int = /-?[0-9]+/
_ = /\s*/
LPAREN = _ '(' _
RPAREN = _ ')' _
EOS;

$grammar = new Grammar($syntax);
$tree = $grammar->parse($argv[1]);
$calculator = new Calculator([
    '' => 'liftChildren',
    '_' => 'ignore',
    'factor' => 'liftChild',
    'parenthesized' => 'liftChild',
    'number' => 'liftChild',
    'expo' => ['liftValue', 'toFloat'],
    'int' => 'liftValue',
    'float' => 'liftValue',
    'LPAREN' => 'ignore',
    'RPAREN' => 'ignore',
]);
$result = $calculator->visit($tree);
echo "Result: ", $result, "\n";
echo "Mem peak: ", memory_get_peak_usage(), "\n";
