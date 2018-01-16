<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Compiler\Extension\Php\Expression;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Composite;
use ju1ius\Pegasus\CST\Node\Quantifier;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Tests\Compiler\Extension\Php\PhpCompilerTestCase;


class QuantifierTest extends PhpCompilerTestCase
{
    /**
     * @dataProvider parseProvider
     *
     * @param string $syntax
     * @param string $input
     * @param Node $expected
     * @throws \ju1ius\Pegasus\Grammar\Exception\MissingTraitAlias
     */
    public function testParse(string $syntax, string $input, Node $expected)
    {
        $parser = $this->compile($syntax);
        $result = $parser->parse($input);
        $this->assertNodeEquals($expected, $result);
    }

    public function parseProvider()
    {
        return [
            'optional, failure' => [
                'x = "foo"?',
                '',
                new Quantifier('x', 0, 0, [], true),
            ],
            'optional, success' => [
                'x = "foo"?',
                'foo',
                new Quantifier('x', 0, 3, [
                    new Terminal('', 0, 3, 'foo'),
                ], true),
            ],
            'zero or more, failure' => [
                'x = "a"*',
                '',
                new Quantifier('x', 0, 0, [], false),
            ],
            'zero or more, success' => [
                'x = "a"*',
                'aaa',
                new Quantifier('x', 0, 3, [
                    new Terminal('', 0, 1, 'a'),
                    new Terminal('', 1, 2, 'a'),
                    new Terminal('', 2, 3, 'a'),
                ], false),
            ],
            'one or more, one success' => [
                'x = "a"+',
                'a',
                new Quantifier('x', 0, 1, [
                    new Terminal('', 0, 1, 'a'),
                ], false),
            ],
            'one or more, more success' => [
                'x = "a"+',
                'aaa',
                new Quantifier('x', 0, 3, [
                    new Terminal('', 0, 1, 'a'),
                    new Terminal('', 1, 2, 'a'),
                    new Terminal('', 2, 3, 'a'),
                ], false),
            ],
            'exact' => [
                'x = "a"{2}',
                'aa',
                new Quantifier('x', 0, 2, [
                    new Terminal('', 0, 1, 'a'),
                    new Terminal('', 1, 2, 'a'),
                ], false),
            ],
            'between, lower bound' => [
                'x = "a"{1,3}',
                'a',
                new Quantifier('x', 0, 1, [
                    new Terminal('', 0, 1, 'a'),
                ], false),
            ],
            'between, between bounds' => [
                'x = "a"{1,3}',
                'aa',
                new Quantifier('x', 0, 2, [
                    new Terminal('', 0, 1, 'a'),
                    new Terminal('', 1, 2, 'a'),
                ], false),
            ],
            'between, upper bound' => [
                'x = "a"{1,3}',
                'aaa',
                new Quantifier('x', 0, 3, [
                    new Terminal('', 0, 1, 'a'),
                    new Terminal('', 1, 2, 'a'),
                    new Terminal('', 2, 3, 'a'),
                ], false),
            ],
            'unbounded' => [
                'x = "a"{2,}',
                'aaaa',
                new Quantifier('x', 0, 4, [
                    new Terminal('', 0, 1, 'a'),
                    new Terminal('', 1, 2, 'a'),
                    new Terminal('', 2, 3, 'a'),
                    new Terminal('', 3, 4, 'a'),
                ], false),
            ],
        ];
    }

    /**
     * @dataProvider parseFailureProvider
     *
     * @param string $syntax
     * @param string $input
     * @throws \ju1ius\Pegasus\Grammar\Exception\MissingTraitAlias
     */
    public function testParseFailure(string $syntax, string $input)
    {
        $this->expectException(ParseError::class);
        $parser = $this->compile($syntax);
        $parser->parse($input);
    }

    public function parseFailureProvider()
    {
        return [
            ['x = "foo"?', 'bar'],
            ['x = "foo"?', 'foobar'],
            ['x = "a"*', 'b'],
            ['x = "a"+', ''],
            ['x = "a"{3}', 'aa'],
            ['x = "a"{3}', 'aaaa'],
            ['x = "a"{1,3}', ''],
            ['x = "a"{1,3}', 'aaaa'],
            ['x = "a"{3,}', 'aa'],
        ];
    }
}
