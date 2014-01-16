<?php

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Regex;
use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\ZeroOrMore;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Sequence;


/**
 * Grammar class for parsing Pegasus grammar definitions.
 *
 * This is a simple grammar.
 * We may someday add to it, but it's a safe bet
 * that the future will always be a superset of this.
 */
class PegasusGrammar extends Grammar
{
    /**
     * The Pegasus grammar syntax definitions.
     *
     * NB: this is specified as a nowdoc,
     * so double escaping backslashes in regex patterns is not needed
     */
    const SYNTAX = <<<'EOS'
# Ignored things (represented by _) are typically hung off the end of the
# leafmost kinds of nodes. Literals like "|" count as leaves.

rules          = _ rule+
rule           = identifier equals expression

expression     = ored | sequence | term
ored           = sequence or_term+
or_term        = "|" _ sequence
#ored           = term or_term+
#or_term        = "|" _ term
#sequence       = term term+
sequence       = term+
term           = not_term | lookahead_term | quantified | atom
labeled        = label term
not_term       = "!" term _
lookahead_term = "&" term _
quantified     = atom quantifier
atom           = reference | literal | regex | parenthesized
regex          = / \/ ((?: (?:\\.) | [^\/] )*) \/ ([ilmsux]*)? / _
parenthesized  = "(" _ expression ")" _
quantifier     = / ([*+?]) | (?: \{ (\d+)(?:,(\d*))? \} ) / _

literal        = / (["\']) ((?: (?:\\.) | (?:(?!\1).) )*) \1 / _

# A subsequent equal sign is the only thing that distinguishes an identifier
# (which begins a new rule) from a reference (which is just a pointer to a
# rule defined somewhere else):

reference     = identifier !equals
equals        = "=" _
label         = identifier ':'
identifier    = /[a-zA-Z_]\w*/ _

_             = ws_or_comment*
ws_or_comment = ws | comment
ws            = /\s+/
comment       = / \#([^\r\n]*) /
EOS;

    static private $GRAMMAR = null;

    static public function build()
    {
        if (null === self::$GRAMMAR) {
            self::$GRAMMAR = new PegasusGrammar(self::SYNTAX);
            self::$GRAMMAR = new Grammar(self::SYNTAX);
        }
        return self::$GRAMMAR;
    }

    /**
     * Hard-code enough of the rules to parse the grammar
     * that describes the grammar description language,
     * allowing us to bootstrap ourselves.
     */
    public static function getRules()
    {
        $comment = new Regex('\#([^\r\n]*)', 'comment');
        $ws = new Regex('\s+', 'ws');
        $ws_or_comment = new OneOf([$ws, $comment], 'ws_or_comment');
        $_ = new ZeroOrMore([$ws_or_comment], '_');
        $identifier = new Sequence([
            new Regex('[a-zA-Z_]\w*'),
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
        array_unshift($term->children, $not_term);

        //$sequence = new Sequence([$term, new OneOrMore([$term], 'term+')], 'sequence');
        //$or_term = new Sequence([new Literal('|', 'pipe'), $_, $term], 'or_term');
        //$ored = new Sequence([$term, new OneOrMore([$or_term], 'or_term+')], 'ored');
        $sequence = new OneOrMore([$term], 'sequence');
        $or_term = new Sequence([new Literal('|', 'pipe'), $_, $sequence], 'or_term');
        $ored = new Sequence([$sequence, new OneOrMore([$or_term], 'or_term+')], 'ored');
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

        return $rules;
    }

    /**
     * Return the rules for parsing the grammar definition syntax.
     * 
     * Return a 2-tuple: a dict of rule names pointing to their expressions,
     * and then the top-level expression for the first rule.
     */
    protected function expressionsFromSyntax($syntax)
    {
        // Use the hard-coded rules to parse the (more extensive) rule syntax.
        // (For example, unless I start using parentheses in the rule language
        // definition itself, I should never have to hard-code expressions for
        // those above.)
        $parser = new Parser\Packrat(self::getRules());
        $rule_tree = $parser->parse($syntax);
        // Turn the parse tree into a map of expressions:
        return (new RuleVisitor)->visit($rule_tree);
    }
}
