<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Application;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\ExpressionTestCase;
use ju1ius\Pegasus\Tests\PegasusAssert;

class ReferenceTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
     */
    public function testMatch(Grammar $grammar, array $args, Node $expected)
    {
        PegasusAssert::nodeEquals($expected, self::parse($grammar, ...$args));
    }

    public function provideTestMatch(): iterable
    {
        yield [
            GrammarBuilder::create()
                ->rule('test')->ref('foo')
                ->rule('foo')->literal('foo')
            ->getGrammar(),
            ['foo'],
            new Terminal('foo', 0, 3, 'foo')
        ];
    }
}
