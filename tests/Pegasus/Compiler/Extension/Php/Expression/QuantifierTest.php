<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Compiler\Extension\Php\Expression;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Quantifier;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Tests\Compiler\Extension\Php\PhpCompilerTestCase;
use ju1ius\Pegasus\Tests\PegasusAssert;

class QuantifierTest extends PhpCompilerTestCase
{
    /**
     * @dataProvider parseProvider
     */
    public function testParse(string $syntax, string $input, Node $expected)
    {
        $parser = self::compile($syntax);
        $result = $parser->parse($input);
        PegasusAssert::nodeEquals($expected, $result);
    }

    public function parseProvider(): iterable
    {
        yield 'optional, failure' => [
            'x = "foo"?',
            '',
            new Quantifier('x', 0, 0, [], true),
        ];
        yield 'optional, success' => [
            'x = "foo"?',
            'foo',
            new Quantifier('x', 0, 3, [
                new Terminal('', 0, 3, 'foo'),
            ], true),
        ];
        yield 'zero or more, failure' => [
            'x = "a"*',
            '',
            new Quantifier('x', 0, 0, [], false),
        ];
        yield 'zero or more, success' => [
            'x = "a"*',
            'aaa',
            new Quantifier('x', 0, 3, [
                new Terminal('', 0, 1, 'a'),
                new Terminal('', 1, 2, 'a'),
                new Terminal('', 2, 3, 'a'),
            ], false),
        ];
        yield 'one or more, one success' => [
            'x = "a"+',
            'a',
            new Quantifier('x', 0, 1, [
                new Terminal('', 0, 1, 'a'),
            ], false),
        ];
        yield 'one or more, more success' => [
            'x = "a"+',
            'aaa',
            new Quantifier('x', 0, 3, [
                new Terminal('', 0, 1, 'a'),
                new Terminal('', 1, 2, 'a'),
                new Terminal('', 2, 3, 'a'),
            ], false),
        ];
        yield 'exact' => [
            'x = "a"{2}',
            'aa',
            new Quantifier('x', 0, 2, [
                new Terminal('', 0, 1, 'a'),
                new Terminal('', 1, 2, 'a'),
            ], false),
        ];
        yield 'between, lower bound' => [
            'x = "a"{1,3}',
            'a',
            new Quantifier('x', 0, 1, [
                new Terminal('', 0, 1, 'a'),
            ], false),
        ];
        yield 'between, between bounds' => [
            'x = "a"{1,3}',
            'aa',
            new Quantifier('x', 0, 2, [
                new Terminal('', 0, 1, 'a'),
                new Terminal('', 1, 2, 'a'),
            ], false),
        ];
        yield 'between, upper bound' => [
            'x = "a"{1,3}',
            'aaa',
            new Quantifier('x', 0, 3, [
                new Terminal('', 0, 1, 'a'),
                new Terminal('', 1, 2, 'a'),
                new Terminal('', 2, 3, 'a'),
            ], false),
        ];
        yield 'unbounded' => [
            'x = "a"{2,}',
            'aaaa',
            new Quantifier('x', 0, 4, [
                new Terminal('', 0, 1, 'a'),
                new Terminal('', 1, 2, 'a'),
                new Terminal('', 2, 3, 'a'),
                new Terminal('', 3, 4, 'a'),
            ], false),
        ];
    }

    /**
     * @dataProvider parseFailureProvider
     */
    public function testParseFailure(string $syntax, string $input)
    {
        $this->expectException(ParseError::class);
        $parser = self::compile($syntax);
        $parser->parse($input);
    }

    public function parseFailureProvider(): iterable
    {
        yield ['x = "foo"?', 'bar'];
        yield ['x = "foo"?', 'foobar'];
        yield ['x = "a"*', 'b'];
        yield ['x = "a"+', ''];
        yield ['x = "a"{3}', 'aa'];
        yield ['x = "a"{3}', 'aaaa'];
        yield ['x = "a"{1,3}', ''];
        yield ['x = "a"{1,3}', 'aaaa'];
        yield ['x = "a"{3,}', 'aa'];
    }
}
