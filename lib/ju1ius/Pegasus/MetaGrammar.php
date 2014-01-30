<?php

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Expression\Reference as Ref;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Regex;
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\ZeroOrMore;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Sequence;

use ju1ius\Pegasus\Parser\LRPackrat as Parser;
use ju1ius\Pegasus\Visitor\MetaGrammarNodeVisitor;


/**
 * Factory class that builds a Grammar instance
 * capable of parsing other grammars.
 *
 */
class MetaGrammar
{
	const SYNTAX = <<<'EOS'

grammar			= _ directives rules

########### Directives ##########

directives      = directive*
directive       = name_directive | ws_directive | ci_directive
name_directive  = "%name" _ identifier
ws_directive    = "%whitespace" _ equals expression
ci_directive    = "%case_insensitive" _
	
########### Rules ##########

rules			= rule+

rule			= identifier equals expression

expression		= choice | sequence | term

choice			= alternative (OR alternative)+
			
alternative		= sequence | term

sequence		= term{2}

term			= labeled | labelable

labeled			= label labelable

labelable		= prefixed | prefixable

prefixed		= skip | lookahead | not

skip            = '~' prefixable

lookahead		= '&' prefixable

not				= '!' prefixable

prefixable		= prefixed | suffixable | primary

suffixable		= suffixed | primary

suffixed		= suffixable quantifier

primary			= parenthesized | atom

parenthesized	= "(" _ expression ")" _
			
atom			= literal | regex | reference

equals			= "=" _

reference		= identifier !equals

quantifier		= /(?> ([*+?]) | (?: \{ (\d+)(?:,(\d*))? \} ) )/ _

regex			= / \/ ((?: (?:\\\\.) | [^\/] )*) \/ ([ilmsux]*)? / _

literal			= / (["\']) ((?: (?:\\.) | (?:(?!\1).) )*) \1 / _

label			= / ([a-zA-Z_]\w*): /

identifier		= / [a-zA-Z_]\w* / _

OR				= "|" _

_				= (ws | comment)*

comment			= / \# ([^\r\n]*) /

ws				= /\s+/

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
	private function __construct(){}

	/**
	 * Factory method for MetaGrammar.
	 *
     * @return Grammar
	 */
	public static function create()
	{
		if (null === self::$instance) {
            $grammar = self::getGrammar();
			// FIXME: ATM this is a bit overkill to parse the syntax
			// since it matches exactly the expression tree.
			// we should find a way to simplify the expression tree in order
			// to speedup the syntax parsing process.
			$parser = new Parser($grammar);
			$tree = $parser->parseAll(self::SYNTAX);
			list($rules, $default) = (new MetaGrammarNodeVisitor)->visit($tree);
			self::$instance = new Grammar($rules, $default);
            //echo self::$instance, "\n";
            self::$instance->finalize();
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
		$g = new Grammar();
		$g['grammar'] = new Sequence([
			new Ref('_'), new Ref('rules')
		]);
		$g['rules'] = new OneOrMore([new Ref('rule')]);
		$g['rule'] = new Sequence([
			new Ref('identifier'),
			new Ref('equals'),
			new Ref('expression')
		]);
		$g['expression'] = new OneOf([
			new Ref('choice'),
            new Ref('sequence'),
			new Ref('term')
		]);
		$g['choice'] = new Sequence([
            new Ref('alternative'),
            new OneOrMore([
                new Sequence([
                    new Ref('OR'),
                    new Ref('alternative')
                ])
            ])
        ]);
        $g['alternative'] = new OneOf([
            new Ref('sequence'),
            new Ref('term')
        ]);
        $g['sequence'] = new Quantifier([new Ref('term')], 2, INF);
        $g['term'] = new OneOf([
            new Ref('labeled'),
            new Ref('labelable')
        ]);
		$g['labeled'] = new Sequence([
			new Ref('label'),
			new Ref('labelable'),
			new Ref('_')
		]);
        $g['labelable'] = new OneOf([
            new Ref('prefixed'),
            new Ref('prefixable')
        ]);
		$g['prefixed'] = new OneOf([
			new Sequence([
				new Literal('~'), new Ref('prefixable')
			], 'skip'),
			new Sequence([
				new Literal('&'), new Ref('prefixable')
			], 'lookahead'),
			new Sequence([
				new Literal('!'), new Ref('prefixable')
			], 'not')
		]);
		$g['prefixable'] = new OneOf([
			new Ref('prefixed'),
			new Ref('suffixable'),
			new Ref('primary')
		]);
		$g['suffixable'] = new OneOf([
			new Ref('suffixed'), new Ref('primary')
		]);
		$g['suffixed'] = new Sequence([
			new Ref('suffixable'), new Ref('quantifier')
		]);
		$g['primary'] = new OneOf([
			new Ref('parenthesized'),
			new Ref('atom')
		]);
        $g['parenthesized'] = new Sequence([
				new Literal('('),
				new Ref('_'),
				new Ref('expression'),
				//new Ref('_'),
				new Literal(')'),
				new Ref('_')
        ]);
		$g['atom'] = new OneOf([
			//new Literal ('EOF', 'eof'),
			//new Literal ('E', 'epsilon'),
			new Ref('literal'),
			new Ref('regex'),
			new Ref('reference')
		]);
		$g['OR'] = new Sequence([
			new Literal('|'),
			new Ref('_')
		]);
		$g['equals'] = new Sequence([
			new Literal('='),
			new Ref('_')
		]);
		$g['reference'] = new Sequence([
			new Ref('identifier'),
			new Not([new Ref('equals')])
		]);
		$g['quantifier'] = new Sequence([
			new Regex('(?> ([*+?]) | (?: \{ (\d+) (?:,(\d*))?\} ) )'),
			new Ref('_')
		]);
		$g['literal'] = new Sequence([
			new Regex('(["\'])((?:(?:\\\\.)|(?:(?!\1).))*)\1'),
			new Ref('_')
		]);
		$g['regex'] = new Sequence([
			new Regex('\/((?:(?:\\\\.)|[^\/])*)\/([ilmSux]*)?'),
			new Ref('_')
		]);
		$g['identifier'] = new Sequence([
			new Regex('[a-zA-Z_]\w*'),
			new Ref('_')
		]);
		$g['label'] = new Regex('([a-zA-Z_]\w*):');
		$g['ident'] = new Regex('[a-zA-Z_]\w*');
		$g['_'] = new ZeroOrMore([new Ref('ws_com')]);
		$g['ws_com'] = new OneOf([new Ref('ws'), new Ref('comment')]);
		$g['comment'] = new Regex('\#([^\n]*)');
		$g['ws'] = new Regex('\s+');

        $g->finalize('grammar');

        return $g;
	}
}
