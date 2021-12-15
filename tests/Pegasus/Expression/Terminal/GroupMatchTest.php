<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Terminal;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Expression\Terminal\GroupMatch;
use ju1ius\Pegasus\Expression\Terminal\NonCapturingRegExp;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class GroupMatchTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
     */
    public function testMatch(array $expr, array $matchArgs, Node $expected)
    {
        $this->assertNodeEquals(
            $expected,
            $this->parse($expr, ...$matchArgs)
        );
    }

    public function provideTestMatch(): iterable
    {
        yield 'Single capturing group' => [
            ['test' => new GroupMatch(new NonCapturingRegExp('\s*(\w+)'), 1, 'test')],
            ['   abc123   '],
            new Terminal('test', 0, 9, 'abc123')
        ];
        yield 'Multiple capturing group' => [
            ['test' => new GroupMatch(new NonCapturingRegExp('\s*(\w+)\s+(\w+)'), 2, 'test')],
            ['   abc 123'],
            new Terminal('test', 0, 10, '   abc 123', [
                'captures' => ['abc', '123']
            ])
        ];
    }
}
