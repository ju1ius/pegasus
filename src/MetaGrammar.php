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

use ju1ius\Pegasus\Grammar\Builder;
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
            // FIXME: ATM this is completely overkill to parse the syntax
            // since it matches exactly the expression tree.
            // we should find a way to simplify the expression tree in order
            // to speedup the syntax parsing process.
            /*
            $parser = new Parser($grammar);
            $tree = $parser->parseAll(self::SYNTAX);
            self::$instance = (new MetaGrammarTraverser)->traverse($tree);
            //echo self::$instance, "\n";
            self::$instance->finalize();
            */
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
        $builder = Builder::create()
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
            ->rule('ws_directive')->sequence()
                ->skip()->literal('%whitespace')->ref('_')
                ->skip()->literal('=')->ref('_')
                ->ref('unattributed')
            ->rule('ci_directive')->sequence()
                ->skip()->literal('%case_insensitive')
                ->ref('_')
            ->rule('lexical_directive')->sequence()
                ->skip()->literal('%lexical')
                ->ref('_')
            ->rule('inline_directive')->sequence()
                ->skip()->literal('%inline')
                ->ref('_')
        ;
        //
        // rules
        // ------------------------------------------------------------------------------------------------------
        $builder
            ->rule('rules')->oneOrMore()
                ->ref('rule')
            ->rule('rule')->sequence()
                ->zeroOrMore()->oneOf()
                    ->ref('inline_directive')
                    ->ref('lexical_directive')
                ->end()
                ->ref('identifier')
                ->skip()->literal('=')->ref('_')
                ->ref('expression')
        ;
        //
        // decorator expressions
        // ------------------------------------------------------------------------------------------------------
        $builder
            ->rule('quantifier')->sequence()
                ->regexp('(?> ([*+?]) | (?: \{ (\d+) (?:,(\d*))? \} ) )')
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
            ->rule('literal')->sequence()
                ->regexp('(["\']) ((?:\\\\.|(?!\1).)*) \1')
                ->ref('_')
            ->rule('regexp')->sequence()
                ->regexp('\/ ((?:\\\\.|[^\/])*) \/ ([imsuUX]*)?')
                ->ref('_')
            ->rule('eof')->sequence()
                ->match('EOF\b')
                ->ref('_')
            ->rule('epsilon')->sequence()
                ->match('E\b')
                ->ref('_')
            ->rule('fail')->sequence()
                ->match('FAIL\b')
                ->ref('_')
        ;
        //
        // expression parts
        // ------------------------------------------------------------------------------------------------------
        $builder
            ->rule('unattributed')->oneOf()
                ->named('OneOf')
                    ->ref('unattributed')
                    ->skip()->literal('|')->ref('_')
                    ->ref('terms')
                ->end()
                ->ref('terms')
            ->rule('expression')->oneOf()
                ->named('OneOf')
                    ->ref('expression')
                    ->skip()->literal('|')->ref('_')
                    ->ref('attributed')
                ->end()
                ->ref('attributed')
            ->rule('attributed')->oneOf()
                ->named('NamedSequence')
                    ->optional()->ref('attributed')
                    ->skip()->literal('<=')->ref('_')
                    ->ref('identifier')
                ->end()
                ->ref('attributed_terms')
            ->rule('attributed_terms')->oneOf()
                ->named('Sequence')
                    ->ref('attributed')
                    ->ref('term')
                ->end()
                ->ref('terms')
            ->rule('terms')->oneOf()
                ->named('Sequence')
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
                ->ref('regexp')
                ->ref('reference')
            ->rule('identifier')->sequence()
                ->match('[a-zA-Z_]\w*')
                ->ref('_')
            ->rule('label')->sequence()
                ->match('[a-zA-Z_]\w*')
                ->skip()->literal(':')
        ;
        //
        // whitespace
        // ------------------------------------------------------------------------------------------------------
        $builder
            ->rule('_')->skip()->zeroOrMore()
                ->oneOf()
                    ->ref('ws')
                    ->ref('comment')
            ->rule('ws')
                ->match('\s+')
            ->rule('comment')
                ->match('\#[^\n]*')
            ->getGrammar()
        ;

        $grammar = $builder->getGrammar();
        $grammar->inline('ws', 'comment');

        return $grammar;
    }
}
