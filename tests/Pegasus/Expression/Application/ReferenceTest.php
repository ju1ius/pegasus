<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Application;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class ReferenceTest extends ExpressionTestCase
{
    /**
     * @dataProvider provideTestMatch
     *
     * @param string $grammar
     * @param array  $args
     * @param Node   $expected
     */
    public function testMatch($grammar, $args, $expected)
    {
        $this->assertNodeEquals($expected, $this->parse($grammar, ...$args));
    }

    public function provideTestMatch()
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
