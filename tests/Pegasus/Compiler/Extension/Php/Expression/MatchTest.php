<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Tests\Compiler\Extension\Php\Expression;


use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Tests\Compiler\Extension\Php\PhpCompilerTestCase;


class MatchTest extends PhpCompilerTestCase
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
        yield [
            'x = /foo|bar/',
            'foo',
            new Node\Terminal('x', 0, 3, 'foo'),
        ];
        yield [
            'x = /foo|bar/',
            'bar',
            new Node\Terminal('x', 0, 3, 'bar'),
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
            yield ['x = /foo|bar/', 'baz'];
            yield ['x = /foo|bar/', 'foobar'];
    }
}
