<?php

require_once __DIR__.'/../../vendor/autoload.php';

use ju1ius\Pegasus\PegasusGrammar;
use ju1ius\Pegasus\Packrat\Parser;
use ju1ius\Pegasus\RuleVisitor;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Regex;
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\ZeroOrMore;
use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\Lookahead;

// just for docs ...
$string_pattern = '@\G
    (["\'])             # A string delimiter
    ((?:                # 0 or more
        (?:\\\\.)       # escaped character
        |               # OR
        (?: (?!\1). )   # Not the delimiter followed by anything
    )*)
    \1                  # The closing delimiter
@x';
$regex_pattern = '@\G
    /               # The pattern delimiter
    ((?:            # 0 or more
        (?:\\\\.)   # escaped character
        |           # or
        [^/]        # Not the delimiter
    )*)
    /               # The closing delimiter
    ([ilmsux]*)?    # Optional flags
@x';

//$regex = new Regex('\/((?:(?:\\\\.)|[^\/])*)\/([ilmsux]*)?');
//$rx_rule = <<<'EOS'
///\/((?:(?:\\.)|[^\/])*)\/([ilmsux]*)?/
//EOS;
//$node = $regex->match($rx_rule);
//$rx_rx = new Regex($node->match[1][0]);
//$node = $rx_rx->match('/foo\/bar/i');
//var_dump($node);

//$metagrammar = PegasusGrammar::build();
//$my_syntax = <<<'EOS'
//some_rule = ('foo' 'bar')
    //| ('bar' 'baz')
    //| ('foo')
//EOS;
//$tree = $metagrammar->parse($my_syntax);
//list($rules, $default) = (new RuleVisitor)->visit($tree);
//var_dump($rules);

//$_ = new Regex('\s*', '_');
//$id = new Regex('\w+', 'id');
//$eq = new Literal ('=', 'eq');
//$expr = new Sequence([
    //$id, $_, $eq, $_, $id, $_
//], 'expr');
//$rules = new Sequence([
    //$_, new OneOrMore([$expr], 'expr+')
//], 'rules');
//
$rules = PegasusGrammar::getRules();
$parser = new Parser($rules);
$test = <<<'EOS'
myrule = 'foo'
other = 'bar'
EOS;
var_dump(strlen($test));
$tree = $parser->parse($test);
var_dump($tree);
//$result = (new RuleVisitor)->visit($tree);
