<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Compiler\Extension\Php\Expression;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Tests\Compiler\Extension\Php\PhpCompilerTestCase;


class LiteralTest extends PhpCompilerTestCase
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
            [
                'x = "foo"',
                'foo',
                new Terminal('x', 0, 3, 'foo'),
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
            ['x = "foo"', 'bar'],
            ['x = "foo"', 'foobar'],
        ];
    }
}
