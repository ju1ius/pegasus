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

/**
 * Factory class that builds a Grammar instance
 * capable of parsing other grammars.
 *
 */
class MetaGrammar
{
    const SYNTAX = <<<'EOS'

%name Pegasus

grammar <- _ directives rules

########### Directives ##########

directives      <- directive*
directive       <- name_directive | start_directive | ws_directive | ci_directive
name_directive  <- '%name' _ identifier
start_directive <- '%start' _ identifier
ws_directive    <- '%whitespace' _ equals expression
ci_directive    <- '%case_insensitive' _

########### Rules ##########

rules           <- rule+

rule            <- identifier arrow_left expression

expression      <- choice | sequence | term

choice          <- alternative (OR alternative)+
            
alternative     <- sequence | term

sequence        <- term{2,}

term            <- labeled | labelable

labeled         <- label labelable

labelable       <- prefixed | prefixable

prefixed        <- skip | lookahead | not

skip            <- '~' prefixable

lookahead       <- '&' prefixable

not             <- '!' prefixable

prefixable      <- prefixed | suffixable | primary

suffixable      <- suffixed | primary

suffixed        <- suffixable quantifier

primary         <- parenthesized | atom

parenthesized   <- '(' _ expression ')' _

atom            <- eof | epsilon | fail | literal | regex | reference

equals          <- '=' _

arrow_left      <- '<-' _

reference       <- identifier !arrow_left

eof             <- / EOF\b / _

epsilon         <- / E\b / _

fail            <- / FAIL\b / _

quantifier      <- /(?> ([*+?]) | (?: \{ (\d+)(?:,(\d*))? \} ) )/ _

regex           <- / \/ ((?: (?:\\.) | [^\/] )*) \/ ([imsuUX]*)? / _

literal         <- / (["']) ((?: (?:\\.) | (?:(?!\1).) )*) \1 / _

label           <- / ([a-zA-Z_]\w*): /

identifier      <- / [a-zA-Z_]\w* / _

OR              <- '|' _

_               <- (ws | comment)*

comment         <- / \# ([^\r\n]*) /

ws              <- /\s+/

EOS;

    /**
     * @var Grammar The unique instance of the meta grammar.
     */
    private static $instance = null;

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
            self::$instance = (new MetaGrammarNodeVisitor)->visit($tree);
            //echo self::$instance, "\n";
            self::$instance->finalize();
            */
            self::$instance = $grammar;
        }

        return self::$instance;
    }

    /**
     * Returns the unique instance of the base grammar
     * used to parse the MetaGrammar syntax.
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
        $g = Builder::create()
            ->rule('grammar')->sequence()
                ->ref('_')
                ->ref('directives')
                ->ref('rules')
            //
            // Directives
            // ------------------------------------------------------------------------------------------------------
            ->rule('directives')->zeroOrMore()
                ->ref('directive')
            ->rule('directive')->oneOf()
                ->ref('name_directive')
                ->ref('start_directive')
                ->ref('ws_directive')
                ->ref('ci_directive')
            ->rule('name_directive')->sequence()
                ->literal('%name')
                ->ref('_')
                ->ref('identifier')
            ->rule('start_directive')->sequence()
                ->literal('%start')
                ->ref('_')
                ->ref('identifier')
            ->rule('ws_directive')->sequence()
                ->literal('%whitespace')
                ->ref('equals')
                ->ref('expression')
            ->rule('ci_directive')->sequence()
                ->literal('%case_insensitive')
                ->ref('_')
            //
            // rules
            // ------------------------------------------------------------------------------------------------------
            ->rule('rules')->oneOrMore()
                ->ref('rule')
            ->rule('rule')->sequence()
                ->ref('identifier')
                ->ref('arrow_left')
                ->ref('expression')
            //
            // composite expressions
            // ------------------------------------------------------------------------------------------------------
            ->rule('choice')->sequence()
                ->ref('alternative')
                ->oneOrMore()->sequence()
                    ->ref('OR')
                    ->ref('alternative')
            ->rule('alternative')->oneOf()
                ->ref('sequence')
                ->ref('term')
            ->rule('sequence')
                ->atLeast(2)->ref('term')
            //
            // decorator expressions
            // ------------------------------------------------------------------------------------------------------
            ->rule('quantifier')->sequence()
                ->regex('(?> ([*+?]) | (?: \{ (\d+) (?:,(\d*))? \} ) )')
                ->ref('_')
            ->rule('skip')->sequence()
                ->literal('~')
                ->ref('prefixable')
            ->rule('lookahead')->sequence()
                ->literal('&')
                ->ref('prefixable')
            ->rule('not')->sequence()
                ->literal('!')
                ->ref('prefixable')
            //
            // terminal expressions
            // ------------------------------------------------------------------------------------------------------
            ->rule('reference')->sequence()
                ->ref('identifier')
                ->not()->ref('arrow_left')
            ->rule('literal')->sequence()
                ->regex('(["\']) ((?:(?:\\\\.)|(?:(?!\1).))*) \1')
                ->ref('_')
            ->rule('regex')->sequence()
                ->regex('\/((?:(?:\\\\.)|[^\/])*)\/([imsuUX]*)?')
                ->ref('_')
            ->rule('eof')->sequence()
                ->regex('EOF\b')
                ->ref('_')
            ->rule('epsilon')->sequence()
                ->regex('E\b')
                ->ref('_')
            ->rule('fail')->sequence()
                ->regex('FAIL\b')
                ->ref('_')
            //
            // expression parts
            // ------------------------------------------------------------------------------------------------------
            ->rule('expression')->oneOf()
                ->ref('choice')
                ->ref('sequence')
                ->ref('term')
            ->rule('term')->oneOf()
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
                ->ref('lookahead')
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
                ->literal('(')
                ->ref('_')
                ->ref('expression')
                ->literal(')')
                ->ref('_')
            ->rule('atom')->oneOf()
                ->ref('eof')
                ->ref('epsilon')
                ->ref('fail')
                ->ref('literal')
                ->ref('regex')
                ->ref('reference')
            ->rule('OR')->sequence()
                ->literal('|')
                ->ref('_')
            ->rule('arrow_left')->sequence()
                ->literal('<-')
                ->ref('_')
            ->rule('equals')->sequence()
                ->literal('=')
                ->ref('_')
            ->rule('identifier')->sequence()
                ->regex('[a-zA-Z_]\w*')
                ->ref('_')
            ->rule('label')
                ->regex('([a-zA-Z_]\w*):')
            //
            // whitespace
            // ------------------------------------------------------------------------------------------------------
            ->rule('_')->zeroOrMore()
                ->oneOf()
                    ->ref('ws')
                    ->ref('comment')
            ->rule('ws')
                ->regex('\s+')
            ->rule('comment')
                ->regex('\#([^\n]*)')
            ->getGrammar()
        ;

        return $g->finalize('grammar');
    }
}
