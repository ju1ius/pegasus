<?php
require_once __DIR__.'/../../vendor/autoload.php';

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Packrat\Parser;

/*
Consider what happens when expr is applied to the input “1-2-3”.
Since the parser’s current position is initially 0,
this application is encoded as APPLY-RULE(expr, 0).
APPLY-RULE, begins by searching the parser’s
memo table for the result of expr at position 0.
The memo table is initially empty, and thus MEMO(expr, 0)
evaluates to NIL, indicating that expr has not yet been used at position 0.
This leads APPLY-RULE to evaluate the body of the expr rule,
which is made up of two choices.
The first choice begins with <expr>, which,
since the parser’s current position is still 0,
is encoded as the familiar APPLY-RULE(expr, 0).
At this point, the memo table remains unchanged and thus we are back
exactly where we started!
The parser is doomed to repeat the same steps forever,
or more precisely, until the computer eventually runs out of stack space.
*/

$syntax = <<<'EOS'
expr = (expr '-' num) | num
num = /[0-9]+/
EOS;
$grammar = new Grammar($syntax);
$parser = new Parser($grammar);
$tree = $parser->parse('1-2-3');
echo $tree->inspect(), "\n";
