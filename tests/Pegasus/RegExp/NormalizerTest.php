<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\RegExp;

use ju1ius\Pegasus\RegExp\Normalizer;
use PHPUnit\Framework\TestCase;


/**
 * @coversDefaultClass ju1ius\Pegasus\RegExp\Formatter
 */
class NormalizerTest extends TestCase
{
    /**
     * @dataProvider normalizeProvider
     * @param string $pattern
     * @param string $expected
     * @param string[] $flags
     */
    public function testNormalize(string $pattern, string $expected, array $flags = ['x'])
    {
        $this->assertSame($expected, Normalizer::normalize($pattern, $flags));
    }

    public function normalizeProvider()
    {
        yield 'removes whitespace' => [
            '( [a-z] | (?! [0-9] ) )*',
            '([a-z]|(?![0-9]))*',
        ];
        yield 'removes inline comments' => [
            '/foo(?# a foo)|bar(?# a bar)|baz(?# or a baz)/',
            '/foo|bar|baz/',
        ];
        yield 'preserves whitespace in character classes' => [
            '[\t \n]',
            '[\t \n]',
        ];
        yield 'preserves hash in character classes' => [
            '[\t#\n]',
            '[\t#\n]',
        ];
        yield 'Initial x flag not set' => [
            'a b c #d(?# this can be removed)',
            'a b c #d',
            [],
        ];
        yield 'Initial x flag not set but overriden in the pattern' => [
            'a b (?x) c d (?-x) f g',
            'a b (?x)cd(?-x) f g',
            [],
        ];
        yield 'handles POSIX character classes' => [
            '[[:alnum:] #] #foo',
            '[[:alnum:] #]',
        ];
        yield 'handles weird character classes' => [
            '[[\] #] #foo',
            '[[\] #]',
        ];
        yield 'handles block comments' => [
            <<<'EOS'
            /
                foo     # literal foo
                |       # or
                (bar)+  # bars
                |
                (?!\R)  # not a newline
            /
            EOS,
            '/foo|(bar)+|(?!\R)/',
        ];
        yield 'respect escape sequences' => [
            <<<'EOS'
            /
                \#foo       # id foo
                |
                \\#bar      # a backslash, then bar is a comment
            /
            EOS,
            '/\#foo|\\\\/',
        ];
        yield 'inline modifiers #1' => [
            'a b (?-x:c d) e (?-x:#f ) g #end',
            'ab(?-x:c d)e(?-x:#f )g',
        ];
        yield 'inline modifiers #2' => [
            'a b (?-x:c d) e (f (?-x) g) h',
            'ab(?-x:c d)e(f(?-x) g)h',
        ];
    }
}
