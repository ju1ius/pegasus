<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\RegExp;

use ju1ius\Pegasus\RegExp\Exception\MissingClosingParenthesis;
use ju1ius\Pegasus\RegExp\Exception\UnmatchedClosingParenthesis;
use ju1ius\Pegasus\RegExp\PCREGroupInfo;
use ju1ius\Pegasus\Tests\PegasusTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class PCREGroupInfoTest extends PegasusTestCase
{
    /**
     * @dataProvider getTestCaptureCountProvider
     *
     * @param string $pattern
     * @param int    $expected
     */
    public function testCaptureCount($pattern, $expected)
    {
        $this->assertSame($expected, PCREGroupInfo::captureCount($pattern));
    }

    public function getTestCaptureCountProvider()
    {
        return [
            ['foo(ba([rz]))', 2],
            ['foo(ba(?:[rz]))', 1],
            ['foo(ba(?>r|z))', 1],
            ['(?>foo|bar)', 0],
        ];
    }

    /**
     * @dataProvider getTestGroupCountProvider
     *
     * @param string $pattern
     * @param int    $expected
     */
    public function testGroupCount($pattern, $expected)
    {
        $this->assertSame($expected, PCREGroupInfo::groupCount($pattern));
    }

    public function getTestGroupCountProvider()
    {
        return [
            ['foo(ba([rz]))', 2],
            ['foo(ba(?:[rz]))', 2],
            ['foo(ba(?>r|z))', 2],
            ['(?>foo|bar)', 1],
        ];
    }

    /**
     * @dataProvider getParseThrowsOnMissingClosingParenthesisProvider
     *
     * @param string $pattern
     */
    public function testParseThrowsOnMissingClosingParenthesis($pattern)
    {
        $this->expectException(MissingClosingParenthesis::class);
        $info = new PCREGroupInfo();
        $info->parse($pattern);
    }

    public function getParseThrowsOnMissingClosingParenthesisProvider()
    {
        return [
            ['foo(bar'],
            ['foo(ba(r|z)'],
            ['(?>foo(ba(r|z))'],
            ['foo(?(?=foo)bar|baz'],
        ];
    }

    /**
     * @dataProvider getParseThrowsOnUnmatchedClosingParenthesisProvider
     *
     * @param string $pattern
     */
    public function testParseThrowsOnUnmatchedClosingParenthesis($pattern)
    {
        $this->expectException(UnmatchedClosingParenthesis::class);
        $info = new PCREGroupInfo();
        $info->parse($pattern);
    }

    public function getParseThrowsOnUnmatchedClosingParenthesisProvider()
    {
        return [
            ['foo)'],
            ['foo\(bar)'],
            ['foo\(ba\(r|z)'],
            ['fooba(?:r|z))'],
        ];
    }

    /**
     * @dataProvider getTestParseProvider
     *
     * @param string $pattern
     * @param array  $expected
     */
    public function testParse($pattern, $expected)
    {
        $info = new PCREGroupInfo();
        $this->assertEquals($expected, $info->parse($pattern));
    }

    public function getTestParseProvider()
    {
        return [
            'Skips escaped parentheses' => [
                'foo\(ba\(r|z\)\)',
                []
            ],
            'Numbered capturing group' => [
                'foo(bar|baz)',
                [
                    [
                        'type' => 'numbered',
                        'capturing' => true,
                        'number' => 1,
                        'start' => 3,
                        'end' => 12,
                        'pattern' => '(bar|baz)',
                    ],
                ],
            ],
            'Named capturing groups' => [
                "(?P<foo>foo)|(?<bar>bar)|(?'baz'baz)",
                [
                    [
                        'type' => 'named',
                        'capturing' => true,
                        'name' => 'foo',
                        'number' => 1,
                        'start' => 0,
                        'end' => 12,
                        'pattern' => '(?P<foo>foo)',
                    ],
                    [
                        'type' => 'named',
                        'capturing' => true,
                        'name' => 'bar',
                        'number' => 2,
                        'start' => 13,
                        'end' => 24,
                        'pattern' => '(?<bar>bar)',
                    ],
                    [
                        'type' => 'named',
                        'capturing' => true,
                        'name' => 'baz',
                        'number' => 3,
                        'start' => 25,
                        'end' => 36,
                        'pattern' => "(?'baz'baz)",
                    ],
                ],
            ],
            'Non-capturing group' => [
                'foo(?:bar|baz)',
                [
                    [
                        'type' => 'noncapturing',
                        'capturing' => false,
                        'start' => 3,
                        'end' => 14,
                        'pattern' => '(?:bar|baz)',
                    ],
                ],
            ],
            'Atomic group' => [
                'foo(?>bar|baz)',
                [
                    [
                        'type' => 'atomic',
                        'capturing' => false,
                        'start' => 3,
                        'end' => 14,
                        'pattern' => '(?>bar|baz)',
                    ],
                ],
            ],
            'Assertions ' => [
                '(?<!foo)foo(?<=foo)bar(?=baz)baz(?!foo)',
                [
                    [
                        'type' => 'assertion',
                        'capturing' => false,
                        'start' => 0,
                        'end' => 8,
                        'pattern' => '(?<!foo)',
                    ],
                    [
                        'type' => 'assertion',
                        'capturing' => false,
                        'start' => 11,
                        'end' => 19,
                        'pattern' => '(?<=foo)',
                    ],
                    [
                        'type' => 'assertion',
                        'capturing' => false,
                        'start' => 22,
                        'end' => 29,
                        'pattern' => '(?=baz)',
                    ],
                    [
                        'type' => 'assertion',
                        'capturing' => false,
                        'start' => 32,
                        'end' => 39,
                        'pattern' => '(?!foo)',
                    ],
                ],
            ],
            'Options setting' => [
                'foo(?i-m:bar|baz)(?J)qux',
                [
                    [
                        'type' => 'setopt',
                        'capturing' => false,
                        'start' => 3,
                        'end' => 17,
                        'pattern' => '(?i-m:bar|baz)',
                    ],
                    [
                        'type' => 'setopt',
                        'capturing' => false,
                        'start' => 17,
                        'end' => 21,
                        'pattern' => '(?J)',
                    ],
                ]
            ],
            'Branch reset' => [
                'foo(?|bar|baz)',
                [
                    [
                        'type' => 'branchreset',
                        'capturing' => false,
                        'start' => 3,
                        'end' => 14,
                        'pattern' => '(?|bar|baz)',
                    ],
                ]
            ],
            'Conditional groups' => [
                'foo(?(?=bar)bar|baz)(?(1)qux)',
                [
                    [
                        'type' => 'conditional',
                        'capturing' => false,
                        'start' => 3,
                        'end' => 20,
                        'pattern' => '(?(?=bar)bar|baz)',
                    ],
                    [
                        'type' => 'condition',
                        'capturing' => false,
                        'start' => 5,
                        'end' => 12,
                        'pattern' => '(?=bar)',
                    ],
                    [
                        'type' => 'conditional',
                        'capturing' => false,
                        'start' => 20,
                        'end' => 29,
                        'pattern' => '(?(1)qux)',
                    ],
                    [
                        'type' => 'condition',
                        'capturing' => false,
                        'start' => 22,
                        'end' => 25,
                        'pattern' => '(1)',
                    ],
                ]
            ],
            'Comments' => [
                'foo(?# its a foo)|bar',
                [
                    [
                        'type' => 'comment',
                        'capturing' => false,
                        'start' => 3,
                        'end' => 17,
                        'pattern' => '(?# its a foo)',
                    ]
                ]
            ],
        ];
    }
}
