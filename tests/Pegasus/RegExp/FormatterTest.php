<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\RegExp;

use ju1ius\Pegasus\RegExp\Formatter;
use PHPUnit\Framework\TestCase;


/**
 * @coversDefaultClass ju1ius\Pegasus\RegExp\Formatter
 */
class FormatterTest extends TestCase
{
    /**
     * @dataProvider removeCommentsProvider
     * @param string $pattern
     * @param string $expected
     */
    public function testRemoveComments(string $pattern, string $expected)
    {
        $this->assertSame($expected, Formatter::removeComments($pattern));
    }

    public function removeCommentsProvider()
    {
        return [
            'removes whitespace' => [
                '( [a-z] | (?! [0-9] ) )*',
                '([a-z]|(?![0-9]))*',
            ],
            'removes inline comments' => [
                '/foo(?# a foo)|bar(?# a bar)|baz(?# or a baz)/',
                '/foo|bar|baz/',
            ],
            'preserves whitespace in character classes' => [
                '[\t \n]',
                '[\t \n]',
            ],
            'preserves hash in character classes' => [
                '[\t#\n]',
                '[\t#\n]',
            ],
            'handles POSIX character classes' => [
                '[[:alnum:] #] #foo',
                '[[:alnum:] #]',
            ],
            'handles weird character classes' => [
                '[[\] #] #foo',
                '[[\] #]',
            ],
            'removes comments & whitespace' => [
                <<<'EOS'
/
    foo     # literal foo
    |       # or
    (bar)+  # bars
    |
    (?!\R)  # not a newline
/
EOS
                , '/foo|(bar)+|(?!\R)/'
            ],
            'respect escape sequences' => [
                <<<'EOS'
/
    \#foo       # id foo
    |
    \\#bar      # a backslash and bar is a comment
/
EOS
                , '/\#foo|\\\\/'
            ]
        ];
    }
}
