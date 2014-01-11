<?php

use ju1ius\Test\Pegasus\PegasusTestCase;

use ju1ius\Pegasus\MetaGrammar;

use ju1ius\Pegasus\Parser\LRPackrat as Parser;
use ju1ius\Pegasus\Node\Terminal as Term;
use ju1ius\Pegasus\Node\Composite as Comp;
use ju1ius\Pegasus\Node\Regex as Rx;


class MetaGrammarTest extends PegasusTestCase
{
    protected static $parser;

    public static function setUpBeforeClass()
    {
        $grammar = MetaGrammar::create();
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
                new Rx('comment', '# A comment', 0, 11, [
                    '# A comment',
                    ' A comment'
                ])
            ],
            [
                "# ends with line\n",
                new Rx('comment', "# ends with line\n", 0, 16, [
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
            ['= ', '= '],
            ["  \n= ", "  \n= "]
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
        $this->assertNodeEquals($expected, $node);
    }
    public function testLiteralProvider()
    {
        return [
            [
                '"qstring\"esc"',
                new Comp('literal', '"qstring\"esc"', 0, 14, [
					new Rx('', '"qstring\"esc"', 0, 14, [
                        '"qstring\"esc"',
                        '"',
                        'qstring\"esc'
                    ]),
                    new Comp('_', '"qstring\"esc"', 14, 14, [])
                ])
            ],
            [
                "'qstring\'esc'",
                new Comp('literal', "'qstring\'esc'", 0, 14, [
                    new Rx('', "'qstring\'esc'", 0, 14, [
                        "'qstring\'esc'",
                        "'",
                        "qstring\'esc"
                    ]),
                    new Comp('_', "'qstring\'esc'", 14, 14, [])
                ])
            ]
        ];
    }

    /**
     * @dataProvider testQuantifierProvider
     */
    public function testQuantifier($input, $expected)
    {
        $this->assertNodeEquals(
            $expected,
            $this->parse('quantifier', $input)
        );
    }
    public function testQuantifierProvider()
    {
        return [
            [
                '*',
                new Comp('quantifier', '*', 0, 1, [
                    new Rx('', '*', 0, 1, [
                        '*', '*'
                    ]),
                    new Comp('_', '*', 1, 1, [])
                ])
            ],
            [
                '+',
                new Comp('quantifier', '+', 0, 1, [
                    new Rx('', '+', 0, 1, [
                        '+', '+'
                    ]),
                    new Comp('_', '+', 1, 1, [])
                ])
            ],
            [
                '?',
                new Comp('quantifier', '?', 0, 1, [
                    new Rx('', '?', 0, 1, [
                        '?', '?'
                    ]),
                    new Comp('_', '?', 1, 1, [])
                ])
            ],
            [
                '{1,2}',
                new Comp('quantifier', '{1,2}', 0, 5, [
                    new Rx('', '{1,2}', 0, 5, [
                        '{1,2}', '', '1', '2'
                    ]),
                    new Comp('_', '{1,2}', 5, 5, [])
                ])
            ],
            [
                '{3,}',
                new Comp('quantifier', '{3,}', 0, 4, [
                    new Rx('', '{3,}', 0, 4, [
                        '{3,}', '', '3', ''
                    ]),
                    new Comp('_', '{3,}', 4, 4, [])
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
        return
        $this->assertNodeEquals(
            $expected,
            $this->parse('rule', $input)
        );
    }
    public function testRuleProvider()
    {
        return [[null, null]];
		//return [
            //[
                //"x = 'y' 'z' | 't'",
                //new Comp('rule', "x = 'y' 'z' | 't'", 0, 12, [
                    //new Comp('identifier', "x = 'y' 'z' | 't'", 0, 2, [
                        //new Rx('', "x = 'y' 'z' | 't'", 0, 1, ['x']),
                        //new Comp('_', "x = 'y' 'z' | 't'", 1, 2, [])
                    //]),
                    //new Comp('equals', "x = 'y' 'z' | 't'", 2, 4, [
                        //new Term('', "x = 'y' 'z' | 't'", 2, 3),
                        //new Comp('_', "x = 'y' 'z' | 't'", 3, 4, [])
                    //]),
                    //new Comp('expression', "x = 'y' 'z' | 't'", 4, 12, [
                        //new Comp('ored', "x = 'y' 'z' | 't'", 4, 12, [
                            
                        //])
                    //])
                //])
            //]
		//];
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
