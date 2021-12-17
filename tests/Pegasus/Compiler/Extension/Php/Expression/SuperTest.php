<?php declare(strict_types=1);

namespace Pegasus\Compiler\Extension\Php\Expression;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\Compiler\Extension\Php\PhpCompilerTestCase;

final class SuperTest extends PhpCompilerTestCase
{
    /**
     * @dataProvider parseProvider
     */
    public function testParse(Grammar $grammar, string $input, Node $expected)
    {
        $this->markTestSkipped('Not implemented yet');

        $parent = GrammarBuilder::create()
            ->rule('foo')->literal('foo')
            ->rule('bar')->literal('bar')
            ->getGrammar();
        $grammar->extends($parent);

        $parser = $this->compile($grammar);
        $result = $parser->parse($input);
        $this->assertNodeEquals($expected, $result);
    }

    public function parseProvider(): iterable
    {
        yield [
            GrammarBuilder::create()
                ->rule('foo')->oneOf()
                    ->literal('foobar')
                    ->super()
                ->getGrammar(),
            'foo',
            new Node\Decorator('foo', 0, 3, new Terminal('foo', 0, 3, 'foo'))
        ];
        yield [
            GrammarBuilder::create()
                ->rule('foo')->oneOf()
                    ->literal('foobar')
                    ->super('bar')
                ->getGrammar(),
            'bar',
            new Node\Decorator('foo', 0, 3, new Terminal('bar', 0, 3, 'bar'))
        ];
    }
}
