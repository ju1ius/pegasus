<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Compiler\Extension\Php\Expression;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Tests\Compiler\Extension\Php\PhpCompilerTestCase;

class OneOfTest extends PhpCompilerTestCase
{
    /**
     * @dataProvider parseProvider
     */
    public function testParse(string $syntax, string $input, Node $expected)
    {
        $parser = $this->compile($syntax);
        $result = $parser->parse($input);
        $this->assertNodeEquals($expected, $result);
    }

    public function parseProvider(): iterable
    {
        yield [
            'x = "foo" | "bar"',
            'foo',
            new Terminal('x', 0, 3, 'foo'),
        ];
        yield [
            'x = "foo" | "bar"',
            'bar',
            new Terminal('x', 0, 3, 'bar'),
        ];
    }

    /**
     * @dataProvider parseFailureProvider
     */
    public function testParseFailure(string $syntax, string $input)
    {
        $this->expectException(ParseError::class);
        $parser = $this->compile($syntax);
        $parser->parse($input);
    }

    public function parseFailureProvider(): iterable
    {
        yield ['x = "foo" | "bar"', 'baz'];
        yield ['x = "foo" | "bar"', 'fooqux'];
    }
}
