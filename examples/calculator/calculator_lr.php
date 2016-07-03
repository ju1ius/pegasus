<?php
require_once __DIR__.'/../vendor/autoload.php';

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Parser\LeftRecursivePackrat as Parser;
use ju1ius\Pegasus\Node\Composite;
use ju1ius\Pegasus\NodeVisitor;


class Calculator extends NodeVisitor
{
    /**
     * Unnamed Node (not a rule reference)
     * discard it and return it's children, if any
     */
    public function generic_visit($node, $children)
    {
        if ($node instanceof Composite) {
            return $children ?: null;
            return $children
                ? 1 === count($children) ? $children[0] : $children
                : null;
        }
        return $node;
    }

    public function visit_expr($node, $children)
    {
        //\Psy\Shell::debug(get_defined_vars());
        return $children[0];
    }

    public function visit_term($node, $children)
    {
        return $children[0];
    }

    public function visit_plus($node, $children)
    {
        list($lhs, $op, $rhs) = $children;
        //\Psy\Shell::debug(get_defined_vars());
        return $lhs + $rhs;
    }
    public function visit_minus($node, $children)
    {
        list($lhs, $op, $rhs) = $children;
        //\Psy\Shell::debug(get_defined_vars());
        return $lhs - $rhs;
    }
    public function visit_mul($node, $children)
    {
        list($lhs, $op, $rhs) = $children;
        //\Psy\Shell::debug(get_defined_vars());
        return $lhs * $rhs;
    }
    public function visit_div($node, $children)
    {
        list($lhs, $op, $rhs) = $children;
        return $lhs / $rhs;
    }

    public function visit_parenthesized($node, $children)
    {
        list($lp, $expr, $rp) = $children;
        return $expr;
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

expr    = expr "+" term
        | expr "-" term
        | term

term    = term "*" primary
        | term "/" primary
        | primary

primary = '(' expr ')'
        | num

num     = expo | float | int

float   = /-?[0-9]*\.[0-9]+/

int     = /-?[0-9]+/

expo    = (float | int) 'e' int

_       = /\s*/

EOS;


$grammar = new Grammar($syntax);
$parser = new LeftRecursivePackrat($grammar);
$tree = $parser->parseAll($argv[1]);
$calculator = new Calculator([
    'ignore' => ['_'],
    'actions' => [
        //'expr' => 'liftChild',
        //'term' => 'liftChild',
        'number' => 'liftChild',
        'primary' => 'liftChild'
    ]
]);
$result = $calculator->visit($tree);
echo "Result: ", $result, "\n";
