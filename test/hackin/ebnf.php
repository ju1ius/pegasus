<?php

require_once __DIR__.'/../../vendor/autoload.php';

use ju1ius\Pegasus\PegasusGrammar;
use ju1ius\Pegasus\Grammar;
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
use ju1ius\Pegasus\Expression\EOF;

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

$comment = new Regex('\#([^\r\n]*)', 'comment');
$ws = new Regex('\s+', 'ws');
$ws_or_comment = new OneOf([$ws, $comment], 'ws_or_comment');
$_ = new ZeroOrMore([$ws_or_comment], '_');
$identifier = new Sequence([
    new Regex('[a-zA-Z_][\w]*'),
    $_
], 'identifier');
$literal = new Sequence([
    new Regex('(["\'])((?:(?:\\\\.)|(?:(?!\1).))*)\1', 'literal_rx'),
    $_
], 'literal');
$regex = new Sequence([
    new Regex('\/((?:(?:\\\\.)|[^\/])*)\/([ilmsux]*)?', 'regex_rx'),
    $_
], 'regex');
$quantifier = new Sequence([
    new Regex('([*+?])|(?:\{(\d+)(?:,(\d*))?\})', 'quantifier_rx'),
    $_
], 'quantifier');

$equals = new Sequence([new Literal('=', 'eq'), $_], 'equals');
$reference = new Sequence([
    $identifier,
    new Not([$equals], 'no_eq')
], 'reference');

$atom = new OneOf([$reference, $literal, $regex], 'atom');
$quantified = new Sequence([$atom, $quantifier], 'quantified');

$term = new OneOf([$quantified, $atom], 'term');
$not_term = new Sequence([new Literal('!', 'bang'), $term, $_], 'not_term');
array_unshift($term->members, $not_term);

$sequence = new Sequence([$term, new OneOrMore([$term], 'term+')], 'sequence');
$or_term = new Sequence([new Literal('|', 'pipe'), $_, $term], 'or_term');
$ored = new Sequence([$term, new OneOrMore([$or_term], 'or_term+')], 'ored');
$expression = new OneOf([$ored, $sequence, $term], 'expression');
$rule = new Sequence([
    $identifier,
    $equals,
    $expression
    //new OneOrMore([$expression])
], 'rule');
$rules = new Sequence([
    $_,
    new OneOrMore([$rule], 'rule+')
], 'rules');


$peg = PegasusGrammar::build();
//$parser = new Parser(PegasusGrammar::getRules());
//$tree = $parser->parse(PegasusGrammar::SYNTAX);
////if ($tree) echo $tree->treeView(), "\n";
//$result = (new RuleVisitor)->visit($tree);
var_dump($peg);
