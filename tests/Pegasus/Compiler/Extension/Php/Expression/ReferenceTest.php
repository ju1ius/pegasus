<?php declare(strict_types=1);

namespace Pegasus\Compiler\Extension\Php\Expression;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Tests\Compiler\Extension\Php\PhpCompilerTestCase;
use ju1ius\Pegasus\Tests\PegasusAssert;

final class ReferenceTest extends PhpCompilerTestCase
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
        yield [
            'test=foo foo="foo"',
            'foo',
            new Terminal('foo', 0, 3, 'foo'),
        ];
    }
}
