<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus;

use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Grammar\Optimizer;

/**
 * Factory class that builds a Grammar instance capable of parsing other grammars.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
final class MetaGrammar
{
    /**
     * @var Grammar The unique instance of the optimized meta grammar.
     */
    private static $instance = null;

    /**
     * @var Grammar Unique instance of the unoptimized grammar.
     */
    private static $grammar = null;

    /**
     * Private constructor.
     *
     * You can't instanciate MetaGrammar.
     * You just call MetaGrammar::create() and it returns an unique instance of Grammar.
     */
    private function __construct()
    {
    }

    /**
     * Factory method for MetaGrammar.
     *
     * @return Grammar
     */
    public static function create()
    {
        if (null === self::$instance) {
            $grammar = self::getGrammar();
            self::$instance = Optimizer::optimize($grammar, Optimizer::LEVEL_2);
        }

        return self::$instance;
    }

    /**
     * Returns the unique instance of the base grammar used to parse the MetaGrammar syntax.
     *
     * @return Grammar
     */
    public static function getGrammar()
    {
        if (null === self::$grammar) {
            self::$grammar = self::buildGrammar();
        }

        return self::$grammar;
    }

    private static function buildGrammar()
    {
        $builder = GrammarBuilder::create()
            ->rule('grammar')->sequence()
                ->ref('_')
                ->ref('directives')
                ->ref('rules')
        ;
        //
        // Directives
        // ------------------------------------------------------------------------------------------------------
        $builder
            ->rule('directives')->zeroOrMore()
                ->ref('directive')
            ->rule('directive')->oneOf()
                ->ref('name_directive')
                ->ref('start_directive')
                ->ref('extends_directive')
                ->ref('ws_directive')
                ->ref('ci_directive')
            ->rule('name_directive')->sequence()
                ->skip()->literal('%name')
                ->ref('_')
                ->ref('identifier')
            ->rule('start_directive')->sequence()
                ->skip()->literal('%start')
                ->ref('_')
                ->ref('identifier')
            ->rule('extends_directive')->sequence()
                ->skip()->literal('%extends')
                ->ref('_')
                ->ref('identifier')
            ->rule('ws_directive')->sequence()
                ->skip()->literal('%whitespace')->ref('_')
                ->skip()->literal('=')->ref('_')
                ->ref('unattributed')
            ->rule('ci_directive')->sequence()
                ->skip()->literal('%case_insensitive')
                ->ref('_')
            ->rule('rule_directive')->oneOf()
                ->named('InlineDirective')->sequence()
                    ->skip()->literal('%inline')
                    ->ref('_')
                ->end()
                ->named('LexicalDirective')->sequence()
                    ->skip()->literal('%lexical')
                    ->ref('_')
                ->end()
        ;
        //
        // rules
        // ------------------------------------------------------------------------------------------------------
        $builder
            ->rule('rules')->zeroOrMore()
                ->ref('rule')
            ->rule('rule')->sequence()
                ->zeroOrMore()->ref('rule_directive')
                ->named('RuleName')->sequence()
                    ->ref('identifier')
                    ->skip()->literal('=')->ref('_')
                ->end()
                ->ref('expression')
        ;
        //
        // decorator expressions
        // ------------------------------------------------------------------------------------------------------
        $builder
            ->rule('quantifier')->sequence()
                ->regexp('(?> (?<symbol>[*+?]) | (?: \{ (?<min>\d+) (?<not_exact>,(?<max>\d*))? \} ) )')
                ->ref('_')
            ->rule('token')->sequence()
                ->skip()->literal('@')
                ->ref('prefixable')
            ->rule('skip')->sequence()
                ->skip()->literal('~')
                ->ref('prefixable')
            ->rule('assert')->sequence()
                ->skip()->literal('&')
                ->ref('prefixable')
            ->rule('not')->sequence()
                ->skip()->literal('!')
                ->ref('prefixable')
        ;
        //
        // terminal expressions
        // ------------------------------------------------------------------------------------------------------
        $builder
            ->rule('reference')->sequence()
                ->ref('identifier')
                ->not()->literal('=')
            ->rule('back_reference')->sequence()
                ->skip()->literal('$')
                ->ref('identifier')
            ->rule('super_call')->sequence()
                ->skip()->word('super')
                ->optional()->sequence()
                    ->skip()->literal('::')
                    ->ref('IDENT')
                ->end()
                ->ref('_')
            ->rule('literal')->sequence()
                ->regexp('(["\']) ((?:\\\\.|(?!\1).)*) \1')
                ->ref('_')
            ->rule('word_literal')->sequence()
                ->skip()->literal('`')
                ->match('(?:\\\\.|[^`])+')
                ->skip()->literal('`')
                ->ref('_')
            ->rule('regexp')->sequence()
                ->regexp('\/ ((?:\\\\.|[^\/])*) \/ ([imsuUX]*)?')
                ->ref('_')
            ->rule('eof')->sequence()
                ->word('EOF')
                ->ref('_')
            ->rule('epsilon')->sequence()
                ->word('E')
                ->ref('_')
            ->rule('fail')->sequence()
                ->word('FAIL')
                ->ref('_')
        ;
        //
        // expression parts
        // ------------------------------------------------------------------------------------------------------
        $builder
            ->rule('unattributed')->oneOf()
                ->named('OneOf')->sequence()
                    ->ref('unattributed')
                    ->skip()->literal('|')->ref('_')
                    ->ref('terms')
                ->end()
                ->ref('terms')
            ->rule('expression')->oneOf()
                ->named('OneOf')->sequence()
                    ->ref('expression')
                    ->skip()->literal('|')->ref('_')
                    ->ref('attributed')
                ->end()
                ->ref('attributed')
            ->rule('attributed')->oneOf()
                ->named('NodeAction')->sequence()
                    ->optional()->ref('attributed')
                    ->skip()->literal('<=')->ref('_')
                    ->ref('identifier')
                ->end()
                ->ref('attributed_terms')
            ->rule('attributed_terms')->oneOf()
                ->named('Sequence')->sequence()
                    ->ref('attributed')
                    ->ref('term')
                ->end()
                ->ref('terms')
            ->rule('terms')->oneOf()
                ->named('Sequence')->sequence()
                    ->ref('terms')
                    ->ref('term')
                ->end()
                ->ref('term')
            ->rule('term')->oneOf()
                ->ref('fail')
                ->ref('labeled')
                ->ref('labelable')
            ->rule('labeled')->sequence()
                ->ref('label')
                ->ref('labelable')
                ->ref('_')
            ->rule('labelable')->oneOf()
                ->ref('prefixed')
                ->ref('prefixable')
            ->rule('prefixed')->oneOf()
                ->ref('skip')
                ->ref('token')
                ->ref('assert')
                ->ref('not')
            ->rule('prefixable')->oneOf()
                ->ref('prefixed')
                ->ref('suffixable')
                ->ref('primary')
            ->rule('suffixed')->sequence()
                ->ref('suffixable')
                ->ref('quantifier')
            ->rule('suffixable')->oneOf()
                ->ref('suffixed')
                ->ref('primary')
            ->rule('primary')->oneOf()
                ->ref('parenthesized')
                ->ref('atom')
            ->rule('parenthesized')->sequence()
                ->skip()->literal('(')->ref('_')
                ->ref('expression')
                ->skip()->literal(')')->ref('_')
            ->rule('atom')->oneOf()
                ->ref('eof')
                ->ref('epsilon')
                ->ref('fail')
                ->ref('literal')
                ->ref('word_literal')
                ->ref('regexp')
                ->ref('back_reference')
                ->ref('super_call')
                ->ref('reference')
            ->rule('identifier')->sequence()
                ->ref('IDENT')
                ->ref('_')
            ->rule('label')->sequence()
                ->ref('IDENT')
                ->skip()->literal(':')
        ;
        $builder->rule('IDENT')->match('[a-zA-Z_]\w*');
        //
        // whitespace
        // ------------------------------------------------------------------------------------------------------
        $builder
            ->rule('_')->skip()->zeroOrMore()->oneOf()
                ->ref('ws')
                ->ref('comment')
            ->rule('ws')
                ->match('\s+')
            ->rule('comment')
                ->match('\#[^\n]*')
        ;

        $grammar = $builder->getGrammar();
        $grammar->inline('IDENT', 'ws', 'comment', '_');

        return $grammar;
    }
}
