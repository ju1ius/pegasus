<?php

namespace ju1ius\Pegasus\Tests;

use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Terminal;
use ju1ius\Pegasus\Parser\LeftRecursivePackrat;

class MetaGrammarTest extends PegasusTestCase
{
    /**
     * @var LeftRecursivePackrat
     */
    private static $parser;

    public static function setUpBeforeClass()
    {
        $grammar = MetaGrammar::create();
        self::$parser = new LeftRecursivePackrat($grammar);
    }

    private function parse($ruleName, $text, $pos = 0)
    {
        return self::$parser->parse($text, $pos, $ruleName);
    }

    /**
     * @dataProvider getCommentProvider
     */
    public function testComment($input, $expected)
    {
        $this->assertNodeEquals(
            $expected,
            $this->parse('comment', $input)
        );
    }

    public function getCommentProvider()
    {
        return [
            [
                '# A comment',
                new Terminal('comment', 0, 11, '# A comment'),
            ],
            [
                "# ends with line\n Nope!",
                new Terminal('comment', 0, 16, "# ends with line"),
            ],
        ];
    }

    /**
     * @depends      testComment
     * @dataProvider getWSProvider
     */
    public function testWS($input, $expected)
    {
        $node = $this->parse('_', $input);
        $this->assertEquals(
            $expected,
            $node->getText($input)
        );
    }
    public function getWSProvider()
    {
        return [
            [
                '# A comment',
                '# A comment',
            ],
            [
                "# comment\n\n\t# comment\n",
                "# comment\n\n\t# comment\n",
            ],
        ];
    }

    /**
     * @depends      testWS
     * @dataProvider getIdentifierProvider
     */
    public function testIdentifier($input, $expected)
    {
        $node = $this->parse('identifier', $input);
        $this->assertEquals($expected, $node->getText($input));
    }

    public function getIdentifierProvider()
    {
        return [
            ['some_ident', 'some_ident'],
            // returns following whitespace
            ['_ident0 = foo', '_ident0 '],
            // stops at invalid character
            ['invalid$ident', 'invalid'],
        ];
    }

    /**
     * @depends      testIdentifier
     * @dataProvider getReferenceProvider
     */
    public function testReference($input, $expected)
    {
        $node = $this->parse('reference', $input);
        $this->assertEquals($expected, $node->getText($input));
    }

    public function getReferenceProvider()
    {
        return [
            ['some_ref', 'some_ref'],
        ];
    }

    /**
     * @dataProvider getReferenceNotEqualsProvider
     * @expectedException \ju1ius\Pegasus\Parser\Exception\ParseError
     */
    public function testReferenceNotEquals($input)
    {
        $node = $this->parse('reference', $input);
    }

    public function getReferenceNotEqualsProvider()
    {
        return [
            ['some_ref = foo'],
            ['some_ref  =  bar'],
        ];
    }

    /**
     * @dataProvider getLiteralProvider
     */
    public function testLiteral($input, $expected)
    {
        $node = $this->parse('literal', $input);
        // test only the regex results
        $node = $node->children[0];
        $this->assertNodeEquals($expected, $node);
    }

    public function getLiteralProvider()
    {
        return [
            'double-quoted with escaped quote' => [
                '"qstring\"esc"',
                new Terminal('', 0, 14, '"qstring\"esc"', [
                    'matches' => ['"qstring\"esc"', '"', 'qstring\"esc']
                ]),
            ],
            'single-quoted with escaped quote' => [
                "'qstring\'esc'",
                new Terminal('', 0, 14, "'qstring\'esc'", [
                    'matches' => ["'qstring\'esc'", "'", "qstring\'esc"]
                ]),
            ],
        ];
    }

    /**
     * @dataProvider getQuantifierProvider
     */
    public function testQuantifier($input, $expected)
    {
        $node = $this->parse('quantifier', $input);
        // test only the regex results
        $node = $node->children[0];
        $this->assertNodeEquals($expected, $node);
    }

    public function getQuantifierProvider()
    {
        return [
            [
                '*',
                new Terminal('', 0, 1, '*', [
                    'matches' => ['*', '*',]
                ]),
            ],
            [
                '+',
                new Terminal('', 0, 1, '+', [
                    'matches' => ['+', '+',]
                ]),
            ],
            [
                '?',
                new Terminal('', 0, 1, '?', [
                    'matches' => ['?', '?',]
                ]),
            ],
            [
                '{1,2}',
                new Terminal('', 0, 5, '{1,2}', [
                    'matches' => ['{1,2}', '', '1', '2',]
                ]),
            ],
            [
                '{3,}',
                new Terminal('', 0, 4, '{3,}', [
                    'matches' => ['{3,}', '', '3', '',]
                ]),
            ],
        ];
    }

    /**
     * @dataProvider getQuantifiedProvider
     */
    public function testQuantified($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function getQuantifiedProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider getRegExpProvider
     */
    public function testRegExp($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function getRegExpProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider getAtomProvider
     */
    public function testAtom($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function getAtomProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider getParenthesizedProvider
     */
    public function testParenthesized($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function getParenthesizedProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider getTermProvider
     */
    public function testTerm($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function getTermProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider getLookaheadTermProvider
     */
    public function testLookaheadTerm($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function getLookaheadTermProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider getNotTermProvider
     */
    public function testNotTerm($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function getNotTermProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider getSequenceProvider
     */
    public function testSequence($input, $expected)
    {
        $node = $this->parse('terms', $input);
        $this->assertEquals($expected, $node->getText($input));
    }

    public function getSequenceProvider()
    {
        return [
            ['foo bar baz', 'foo bar baz']
        ];
    }

    /**
     * @dataProvider getOredProvider
     */
    public function testOred($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function getOredProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider getOrTermProvider
     */
    public function testOrTerm($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function getOrTermProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider getExpressionProvider
     */
    public function testExpression($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function getExpressionProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider getRuleProvider
     */
    public function testRule($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function getRuleProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider getRulesProvider
     */
    public function testRules($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function getRulesProvider()
    {
        return [[null, null]];
    }
}
