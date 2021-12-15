<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Application;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Expression\Application\Super;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class SuperTest extends ExpressionTestCase
{
    public function testMetadata()
    {
        $super = new Super('super');
        $this->assertFalse($super->isCapturingDecidable());
    }

    /**
     * @dataProvider provideTestMatch
     *
     * @param Grammar $grammar
     * @param array   $args
     * @param Node    $expected
     */
    public function testMatch($grammar, $args, $expected)
    {
        $parent = GrammarBuilder::create()
            ->rule('foo')->literal('foo')
            ->rule('bar')->literal('bar')
            ->getGrammar();
        $grammar->extends($parent);
        $this->assertNodeEquals($expected, $this->parse($grammar, ...$args));
    }

    public function provideTestMatch()
    {
        yield [
            GrammarBuilder::create()
                ->rule('foo')->oneOf()
                    ->literal('foobar')
                    ->super()
                ->getGrammar(),
            ['foo'],
            new Node\Decorator('foo', 0, 3, new Terminal('foo', 0, 3, 'foo'))
        ];
        yield [
            GrammarBuilder::create()
                ->rule('foo')->oneOf()
                    ->literal('foobar')
                    ->super('bar')
                ->getGrammar(),
            ['bar'],
            new Node\Decorator('foo', 0, 3, new Terminal('bar', 0, 3, 'bar'))
        ];
    }
}
