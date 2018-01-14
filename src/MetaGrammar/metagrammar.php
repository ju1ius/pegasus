<?php declare(strict_types=1);

use ju1ius\Pegasus\GrammarBuilder;


$QUANTIFIER_PATTERN = <<<'EOS'
(?>
    (?<symbol>[*+?])                        # short quantifier
    |
    (?:
        \{                                  # explicit quantifier
            (?<min>\d+)
            (?<not_exact>,(?<max>\d*))?
        \}
     )
)
EOS;

$STRING_PATTERN = <<<'EOS'
(["'])                  # delimiter
    ((?:                # capture 0 or more
        \\.             # escaped character
        |               # or
        (?!\1) .        # anything but the delimiter
    )*)
\1                      # delimiter
EOS;

$REGEXP_PATTERN = <<<'EOS'
\/                      # delimiter
    ((?:                # capture 0 or more
        \\.             # escaped character
        |               # or
        [^\/]           # anything but the delimiter
    )*)
\/                      # delimiter
([imsuUX]*)?            # optional flags
EOS;

$builder = GrammarBuilder::create();
$builder->rule('grammar')->sequence()
    ->ref('_')
    ->ref('directives')
    ->ref('rules');
//
// Directives
// ------------------------------------------------------------------------------------------------------
$builder->rule('directives')->zeroOrMore()
    ->ref('directive');

$builder->rule('directive')->oneOf()
    ->ref('name_directive')
    ->ref('start_directive')
    ->ref('extends_directive')
    ->ref('import_directive')
    ->ref('ws_directive')
    ->ref('ci_directive');

$builder->rule('name_directive')->sequence()
    ->ignore()->literal('@name')
    ->ref('_')
    ->ref('identifier');

$builder->rule('start_directive')->sequence()
    ->ignore()->literal('@start')
    ->ref('_')
    ->ref('identifier');

$builder->rule('extends_directive')->sequence()
    ->ignore()->literal('@extends')
    ->ref('_')
    ->ref('identifier');

$builder->rule('import_directive')->sequence()
    ->ignore()->literal('@import')->ref('_')
    ->ref('identifier')
    ->ignore()->literal('from')->ref('_')
    ->ref('STRING')->ref('_');

$builder->rule('ws_directive')->sequence()
    ->ignore()->literal('@whitespace')->ref('_')
    ->ignore()->literal('=')->ref('_')
    ->ref('unattributed');

$builder->rule('ci_directive')->sequence()
    ->ignore()->literal('@case_insensitive')
    ->ref('_');

$builder->rule('rule_directive')->oneOf()
    ->named('InlineDirective')->sequence()
        ->ignore()->literal('@inline')
        ->ref('_')
    ->end()
    ->named('LexicalDirective')->sequence()
        ->ignore()->literal('@lexical')
        ->ref('_')
    ->end();
//
// rules
// ------------------------------------------------------------------------------------------------------
$builder->rule('rules')->zeroOrMore()->ref('rule');

$builder->rule('rule')->sequence()
    ->zeroOrMore()->ref('rule_directive')
    ->named('RuleName')->sequence()
        ->ref('identifier')
        ->ignore()->literal('=')->ref('_')
    ->end()
    ->ref('expression');
//
// decorator expressions
// ------------------------------------------------------------------------------------------------------

// Prefix
$builder->rule('token')->sequence()
    ->ignore()->literal('%')
    ->ref('prefixable');

$builder->rule('ignore')->sequence()
    ->ignore()->literal('~')
    ->ref('prefixable');

$builder->rule('assert')->sequence()
    ->ignore()->literal('&')
    ->ref('prefixable');

$builder->rule('not')->sequence()
    ->ignore()->literal('!')
    ->ref('prefixable');

// Postfix
$builder->rule('quantifier')->sequence()
    ->regexp($QUANTIFIER_PATTERN)
    ->ref('_');
$builder->rule('cut')->sequence()
    ->literal('^')
    ->ref('_');

//
// terminal expressions
// ------------------------------------------------------------------------------------------------------
$builder->rule('reference')->sequence()
    ->ref('identifier')
    ->not()->literal('=');

$builder->rule('back_reference')->sequence()
    ->ignore()->literal('$')
    ->ref('identifier');

$builder->rule('super_call')->sequence()
    ->ignore()->word('super')
    ->optional()->sequence()
        ->ignore()->literal('::')
        ->ref('IDENT')
    ->end()
    ->ref('_');

$builder->rule('module_call')->sequence()
    ->ref('IDENT')
    ->ignore()->literal('::')
    ->ref('IDENT')
    ->ref('_');

$builder->rule('literal')->sequence()
    ->ref('STRING')
    ->ref('_');

$builder->rule('word_literal')->sequence()
    ->ignore()->literal('`')
    ->match('(?:\\\\.|[^`])+')
    ->ignore()->literal('`')
    ->ref('_');

$builder->rule('regexp')->sequence()
    ->regexp($REGEXP_PATTERN)
    ->ref('_');

$builder->rule('eof')->sequence()
    ->word('EOF')
    ->ref('_');

$builder->rule('epsilon')->sequence()
    ->word('E')
    ->ref('_');

$builder->rule('fail')->sequence()
    ->word('FAIL')
    ->ref('_');
//
// expression parts
// ------------------------------------------------------------------------------------------------------
$builder->rule('unattributed')->oneOf()
    ->named('OneOf')->sequence()
        ->ref('unattributed')
        ->ignore()->literal('|')->ref('_')
        ->ref('terms')
    ->end()
    ->ref('terms');

$builder->rule('expression')->oneOf()
    ->named('OneOf')->sequence()
        ->ref('expression')
        ->ignore()->literal('|')->ref('_')
        ->ref('attributed')
    ->end()
    ->ref('attributed');

$builder->rule('attributed')->oneOf()
    ->named('NodeAction')->sequence()
        ->optional()->ref('attributed')
        ->ignore()->literal('<=')->ref('_')
        ->ref('identifier')
    ->end()
    ->ref('attributed_terms');

$builder->rule('attributed_terms')->oneOf()
    ->named('Sequence')->sequence()
        ->ref('attributed')
        ->ref('term')
    ->end()
    ->ref('terms');

$builder->rule('terms')->oneOf()
    ->named('Sequence')->sequence()
        ->ref('terms')
        ->ref('term')
    ->end()
    ->ref('term');

$builder->rule('term')->oneOf()
    ->ref('fail')
    ->ref('labeled')
    ->ref('labelable');

$builder->rule('labeled')->sequence()
    ->ref('label')
    ->ref('labelable')
    ->ref('_');

$builder->rule('labelable')->oneOf()
    ->ref('prefixed')
    ->ref('prefixable');

$builder->rule('prefixed')->oneOf()
    ->ref('ignore')
    ->ref('token')
    ->ref('assert')
    ->ref('not');

$builder->rule('prefixable')->oneOf()
    ->ref('prefixed')
    ->ref('suffixable')
    ->ref('primary');

$builder->rule('suffixed')->sequence()
    ->ref('suffixable')
    ->oneOf()
        ->ref('cut')
        ->ref('quantifier');

$builder->rule('suffixable')->oneOf()
    ->ref('suffixed')
    ->ref('primary');

$builder->rule('primary')->oneOf()
    ->ref('parenthesized')
    ->ref('atom');

$builder->rule('parenthesized')->sequence()
    ->ignore()->literal('(')->ref('_')
    ->ref('expression')
    ->ignore()->literal(')')->ref('_');

$builder->rule('atom')->oneOf()
    ->ref('eof')
    ->ref('epsilon')
    ->ref('fail')
    ->ref('literal')
    ->ref('word_literal')
    ->ref('regexp')
    ->ref('back_reference')
    ->ref('super_call')
    ->ref('module_call')
    ->ref('reference');

$builder->rule('identifier')->sequence()
    ->ref('IDENT')
    ->ref('_');

$builder->rule('label')->sequence()
    ->ref('IDENT')
    ->ignore()->literal(':');

$builder->rule('IDENT')->match('[a-zA-Z_]\w*');
$builder->rule('STRING')->regexp($STRING_PATTERN);
//
// whitespace
// ------------------------------------------------------------------------------------------------------
$builder->rule('_')
    ->ignore()->zeroOrMore()->oneOf()
        ->ref('ws')
        ->ref('comment');

$builder->rule('ws')
    ->match('\s+');

$builder->rule('comment')
    ->match('\#[^\n]*');

$grammar = $builder->getGrammar();
$grammar->inline('IDENT', 'ws', 'comment', '_');

return $grammar;
