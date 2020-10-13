<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Tests\Compiler\Extension\Php\Expression;


use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Composite;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Tests\Compiler\Extension\Php\PhpCompilerTestCase;


class SequenceTest extends PhpCompilerTestCase
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
            'x = "foo" "bar"',
            'foobar',
            new Composite('x', 0, 6, [
                new Terminal('', 0, 3, 'foo'),
                new Terminal('', 3, 6, 'bar'),
            ]),
        ];
        yield [
            'x = "foo" "bar" "baz"',
            'foobarbaz',
            new Composite('x', 0, 9, [
                new Terminal('', 0, 3, 'foo'),
                new Terminal('', 3, 6, 'bar'),
                new Terminal('', 6, 9, 'baz'),
            ]),
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
        yield ['x = "foo" "bar"', 'bar'];
        yield ['x = "foo" "bar"', 'foobarbaz'];
    }
}
