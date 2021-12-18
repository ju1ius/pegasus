<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Utils;

use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Utils\Str;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    /**
     * @dataProvider provideTestClassName
     */
    public function testClassName(mixed $input, string $expected)
    {
        Assert::assertSame($expected, Str::className($input));
    }

    public function provideTestClassName(): \Traversable
    {
        yield 'A FQCN as a string' => [
            'Acme\Demo\FooBar',
            'FooBar',
        ];
        yield 'A FQCN in top-level namespace as a string' => [
            '\FooBar',
            'FooBar',
        ];
        yield 'A FQCN in top-level namespace (without leading backslash) as a string' => [
            'stdClass',
            'stdClass',
        ];
        yield 'An stdClass instance' => [
            new \stdClass(),
            'stdClass',
        ];
        yield 'An Expression instance' => [
            new Literal('foo'),
            'Literal',
        ];
    }

    /**
     * @dataProvider provideTestTruncate
     */
    public function testTruncate(array $args, string $expected)
    {
        Assert::assertSame($expected, Str::truncate(...$args));
    }

    public function provideTestTruncate(): \Traversable
    {
        yield 'No truncation' => [
            ['foobar bazqux', 1000],
            'foobar bazqux',
        ];
        yield 'Simple truncation' => [
            ['foobar bazqux', 8],
            'foobar …',
        ];
        yield 'Target column < max width' => [
            ['foobar bazqux', 8, 4],
            'foobar …',
        ];
        yield 'Target column > max width' => [
            ['foobar bazqux', 8, 8],
            '… obar baz',
        ];
    }
}
