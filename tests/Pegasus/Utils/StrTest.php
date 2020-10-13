<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\Utils;

use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Utils\Str;
use PHPUnit\Framework\TestCase;


/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class StrTest extends TestCase
{
    /**
     * @dataProvider provideTestClassName
     *
     * @param object|string $input
     * @param string        $expected
     */
    public function testClassName($input, $expected)
    {
        $this->assertSame($expected, Str::className($input));
    }

    public function provideTestClassName()
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
     *
     * @param array  $args
     * @param string $expected
     */
    public function testTruncate(array $args, $expected)
    {
        $this->assertSame($expected, Str::truncate(...$args));
    }

    public function provideTestTruncate()
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
