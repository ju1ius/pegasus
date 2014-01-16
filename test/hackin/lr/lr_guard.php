<?php
require_once __DIR__.'/../../vendor/autoload.php';

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Packrat\Parser;

/*
3.1 Avoiding Infinite Recursion in Left-Recursive Rules

A simple way to avoid infinite recursion is for APPLY-RULE
to store a result of FAIL in the memo table before it evaluates the body of a rule.
This has the effect of making all left-recursive applications (both direct and indirect) fail.
Consider what happens when expr is applied to the input “1-2-3” using the new version of APPLY-RULE.
Once again this application is encoded as APPLY-RULE(expr, 0).
APPLY-RULE first updates the memo table with a result of FAIL
for expr at position 0, then goes on to evaluate the rule’s body,
starting with its first choice.
The first choice begins with an application of expr, which,
because the current position is still 0, is also encoded as APPLY-RULE(expr, 0).
This time, however, APPLY-RULE will find a result in the memo table,
and thus will not evaluate the body of the rule.
And because that result is FAIL, the current choice will be aborted.
The parser will then move on to the second choice, <num>,
which will succeed after consuming the “1”,
and leave the rest of the input, “-2-3”, unprocessed.
*/

class LR_1Parser extends Parser
{
    public function apply($expr, $pos)
    {
        $this->pos = $pos;
        if($m = $this->memo($expr, $pos)) {
            $this->pos = $m->end;
            return $m->result;
        }
        $m = $this->inject_fail($expr, $pos);
        $result = $expr->match($this->source, $pos, $this);
        $m->result = $result;
        $m->end = $result ? $result->end : $pos;
        return $result;
    }
}

$syntax = <<<'EOS'
expr = (expr '-' num) | num
num = /[0-9]+/
EOS;
$grammar = new Grammar($syntax);
$parser = new LR_1Parser($grammar);
$tree = $parser->parse('1-2-3');
echo $tree->inspect(), "\n";
