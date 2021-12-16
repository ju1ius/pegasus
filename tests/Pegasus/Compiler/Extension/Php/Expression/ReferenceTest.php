<?php declare(strict_types=1);

namespace Pegasus\Compiler\Extension\Php\Expression;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Tests\Compiler\Extension\Php\PhpCompilerTestCase;

final class ReferenceTest extends PhpCompilerTestCase
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
            'test=foo foo="foo"',
            'foo',
            new Terminal('foo', 0, 3, 'foo'),
        ];
    }
}
