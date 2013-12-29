<?php

require_once __DIR__.'/../../Pegasus_TestCase.php';

use ju1ius\Pegasus\PegasusGrammar;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Node\Regex as RegexNode;


class PegasusGrammarTest extends Pegasus_TestCase
{
    protected static $parser;

    public static function setUpBeforeClass()
    {
        $grammar = PegasusGrammar::build();
        self::$parser = new Parser($grammar);
    }

    protected function parse($rule_name, $text, $pos=0)
    {
        return self::$parser->parse($text, $pos, $rule_name);
    }

    /**
     * @dataProvider testCommentProvider
     */
    public function testComment($input, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->parse('comment', $input)
        );
    }
    public function testCommentProvider()
    {
        return [
            [
                '# A comment',
                new RegexNode('comment', '# A comment', 0, 11, [], [
                    '# A comment',
                    ' A comment'
                ])
            ],
            [
                "# ends with line\n",
                new RegexNode('comment', "# ends with line\n", 0, 16, [], [
                    '# ends with line',
                    ' ends with line'
                ])
            ],
        ];
    }
    
    /**
     * @depends testComment
     * @dataProvider test_Provider
     */
    public function test_($input, $expected)
    {
        $node = $this->parse('_', $input);
        $this->assertEquals(
            $expected,
            $node->getText()
        );
    }
    public function test_Provider()
    {
        return [
            [
                '# A comment',
                '# A comment'
            ],
            [
                "# comment\n\n\t# comment\n",
                "# comment\n\n\t# comment\n"
            ],
        ];
    }
 
    /**
     * @depends test_
     * @dataProvider testIdentifierProvider
     */
    public function testIdentifier($input, $expected)
    {
        $node = $this->parse('identifier', $input);
        $this->assertEquals($expected, $node->getText());
    }
    public function testIdentifierProvider()
    {
        return [
            ['some_ident', 'some_ident'],
            // returns following whitespace
            ['_ident0 = foo', '_ident0 '],
            // stops at invalid character
            ['invalid$ident', 'invalid']
        ];
    }

    /**
     * @depends test_
     * @dataProvider testEqualsProvider
     */
    public function testEquals($input, $expected)
    {
        $node = $this->parse('equals', $input);
        $this->assertEquals($expected, $node->getText());
    }
    public function testEqualsProvider()
    {
        return [
            ['=', '='],
            ['= ', '= ']
        ];
    } 

    /**
     * @depends testIdentifier
     * @dataProvider testReferenceProvider
     */
    public function testReference($input, $expected)
    {
        $node = $this->parse('reference', $input);
        $this->assertEquals($expected, $node->getText());
    }
    public function testReferenceProvider()
    {
        return [
            ['some_ref', 'some_ref'],
        ];
    }

    /**
     * @dataProvider testReferenceNotEqualsProvider
     * @expectedException ju1ius\Pegasus\Exception\ParseError
     */
    public function testReferenceNotEquals($input)
    {
        $node = $this->parse('reference', $input);
    }
    public function testReferenceNotEqualsProvider()
    {
        return [
            ['some_ref = foo'],
            ['some_ref   = foo'],
        ];
    }

    /**
     * @dataProvider testLiteralProvider
     */
    public function testLiteral($input, $expected)
    {
        $node = $this->parse('literal', $input);
        $this->assertEquals($expected, $node);
    }
    public function testLiteralProvider()
    {
        return [
            [
                '"qstring\"esc"',
                new Node('literal', '"qstring\"esc"', 0, 14, [
                    new RegexNode('', '"qstring\"esc"', 0, 14, [], [
                        '"qstring\"esc"',
                        '"',
                        'qstring\"esc'
                    ]),
                    new Node('_', '"qstring\"esc"', 14, 14)
                ])
            ],
            [
                "'qstring\'esc'",
                new Node('literal', "'qstring\'esc'", 0, 14, [
                    new RegexNode('', "'qstring\'esc'", 0, 14, [], [
                        "'qstring\'esc'",
                        "'",
                        "qstring\'esc"
                    ]),
                    new Node('_', "'qstring\'esc'", 14, 14)
                ])
            ]
        ];
    }

    /**
     * @dataProvider testQuantifierProvider
     */
    public function testQuantifier($input, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->parse('quantifier', $input)
        );
    }
    public function testQuantifierProvider()
    {
        return [
            [
                '*',
                new Node('quantifier', '*', 0, 1, [
                    new RegexNode('', '*', 0, 1, [], [
                        '*', '*'
                    ]),
                    new Node('_', '*', 1, 1)
                ])
            ],
            [
                '+',
                new Node('quantifier', '+', 0, 1, [
                    new RegexNode('', '+', 0, 1, [], [
                        '+', '+'
                    ]),
                    new Node('_', '+', 1, 1)
                ])
            ],
            [
                '?',
                new Node('quantifier', '?', 0, 1, [
                    new RegexNode('', '?', 0, 1, [], [
                        '?', '?'
                    ]),
                    new Node('_', '?', 1, 1)
                ])
            ],
            [
                '{1,2}',
                new Node('quantifier', '{1,2}', 0, 5, [
                    new RegexNode('', '{1,2}', 0, 5, [], [
                        '{1,2}', '', '1', '2'
                    ]),
                    new Node('_', '{1,2}', 5, 5)
                ])
            ],
            [
                '{3,}',
                new Node('quantifier', '{3,}', 0, 4, [
                    new RegexNode('', '{3,}', 0, 4, [], [
                        '{3,}', '', '3', ''
                    ]),
                    new Node('_', '{3,}', 4, 4)
                ])
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
     * @dataProvider testRegexProvider
     */  
    public function testRegex($input, $expected)
    {
       $this->markTestIncomplete('Test not implemented');
    }
    public function testRegexProvider()
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
       $this->markTestIncomplete('Test not implemented');
    }
    public function testSequenceProvider()
    {
        return [[null, null]];
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
