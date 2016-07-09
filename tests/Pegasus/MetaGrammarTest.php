<?php

namespace ju1ius\Pegasus\Tests;

use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\Node;
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
     * @dataProvider testCommentProvider
     */
    public function testComment($input, $expected)
    {
        $this->assertNodeEquals(
            $expected,
            $this->parse('comment', $input)
        );
    }

    public function testCommentProvider()
    {
        return [
            [
                '# A comment',
                new Node('comment', 0, 11, '# A comment'),
            ],
            [
                "# ends with line\n Nope!",
                new Node('comment', 0, 16, "# ends with line"),
            ],
        ];
    }

    /**
     * @depends      testComment
     * @dataProvider testWSProvider
     */
    public function testWS($input, $expected)
    {
        $node = $this->parse('_', $input);
        $this->assertEquals(
            $expected,
            $node->getText($input)
        );
    }
    public function testWSProvider()
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
     * @dataProvider testIdentifierProvider
     */
    public function testIdentifier($input, $expected)
    {
        $node = $this->parse('identifier', $input);
        $this->assertEquals($expected, $node->getText($input));
    }

    public function testIdentifierProvider()
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
     * @depends      test_
     * @dataProvider testArrowLeftProvider
     */
    public function testArrowLeft($input, $expected)
    {
        $node = $this->parse('arrow_left', $input);
        $this->assertEquals($expected, $node->getText($input));
    }

    public function testArrowLeftProvider()
    {
        return [
            ['<-', '<-'],
            ['<- ', '<- '],
            ["<-\n ", "<-\n "],
        ];
    }

    /**
     * @depends      testIdentifier
     * @dataProvider testReferenceProvider
     */
    public function testReference($input, $expected)
    {
        $node = $this->parse('reference', $input);
        $this->assertEquals($expected, $node->getText($input));
    }

    public function testReferenceProvider()
    {
        return [
            ['some_ref', 'some_ref'],
        ];
    }

    /**
     * @dataProvider testReferenceNotEqualsProvider
     * @expectedException \ju1ius\Pegasus\Exception\ParseError
     */
    public function testReferenceNotEquals($input)
    {
        $node = $this->parse('reference', $input);
    }

    public function testReferenceNotEqualsProvider()
    {
        return [
            ['some_ref <- foo'],
            ['some_ref  <-  bar'],
        ];
    }

    /**
     * @dataProvider testLiteralProvider
     */
    public function testLiteral($input, $expected)
    {
        $node = $this->parse('literal', $input);
        // test only the regex results
        $node = $node->children[0];
        $this->assertNodeEquals($expected, $node);
    }

    public function testLiteralProvider()
    {
        return [
            'double-quoted with escaped quote' => [
                '"qstring\"esc"',
                new Node('', 0, 14, '"qstring\"esc"', [], [
                    'matches' => ['"qstring\"esc"', '"', 'qstring\"esc']
                ]),
            ],
            'single-quoted with escaped quote' => [
                "'qstring\'esc'",
                new Node('', 0, 14, "'qstring\'esc'", [], [
                    'matches' => ["'qstring\'esc'", "'", "qstring\'esc"]
                ]),
            ],
        ];
    }

    /**
     * @dataProvider testQuantifierProvider
     */
    public function testQuantifier($input, $expected)
    {
        $node = $this->parse('quantifier', $input);
        // test only the regex results
        $node = $node->children[0];
        $this->assertNodeEquals($expected, $node);
    }

    public function testQuantifierProvider()
    {
        return [
            [
                '*',
                new Node('', 0, 1, '*', [], [
                    'matches' => ['*', '*',]
                ]),
            ],
            [
                '+',
                new Node('', 0, 1, '+', [], [
                    'matches' => ['+', '+',]
                ]),
            ],
            [
                '?',
                new Node('', 0, 1, '?', [], [
                    'matches' => ['?', '?',]
                ]),
            ],
            [
                '{1,2}',
                new Node('', 0, 5, '{1,2}', [], [
                    'matches' => ['{1,2}', '', '1', '2',]
                ]),
            ],
            [
                '{3,}',
                new Node('', 0, 4, '{3,}', [], [
                    'matches' => ['{3,}', '', '3', '',]
                ]),
            ],
        ];
    }

    /**
     * @dataProvider testQuantifiedProvider
     */
    public function testQuantified($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function testQuantifiedProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider testRegExpProvider
     */
    public function testRegExp($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function testRegExpProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider testAtomProvider
     */
    public function testAtom($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function testAtomProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider testParenthesizedProvider
     */
    public function testParenthesized($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function testParenthesizedProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider testTermProvider
     */
    public function testTerm($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function testTermProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider testLookaheadTermProvider
     */
    public function testLookaheadTerm($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function testLookaheadTermProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider testNotTermProvider
     */
    public function testNotTerm($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function testNotTermProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider testSequenceProvider
     */
    public function testSequence($input, $expected)
    {
        $node = $this->parse('terms', $input);
        $this->assertEquals($expected, $node->getText($input));
    }

    public function testSequenceProvider()
    {
        return [
            ['foo bar baz', 'foo bar baz']
        ];
    }

    /**
     * @dataProvider testOredProvider
     */
    public function testOred($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function testOredProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider testOrTermProvider
     */
    public function testOrTerm($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function testOrTermProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider testExpressionProvider
     */
    public function testExpression($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function testExpressionProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider testRuleProvider
     */
    public function testRule($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function testRuleProvider()
    {
        return [[null, null]];
    }

    /**
     * @dataProvider testRulesProvider
     */
    public function testRules($input, $expected)
    {
        $this->markTestIncomplete('Test not implemented');
    }

    public function testRulesProvider()
    {
        return [[null, null]];
    }
}
