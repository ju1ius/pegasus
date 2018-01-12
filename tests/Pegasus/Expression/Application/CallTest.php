<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Application;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression\Application\Call;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\ExpressionTestCase;


/**
 * @coversDefaultClass ju1ius\Pegasus\Expression\Application\Call
 */
class CallTest extends ExpressionTestCase
{
    public function testGetters()
    {
        $call = new Call('foo', 'bar');
        $this->assertSame('foo', $call->getNamespace());
        $this->assertSame('bar', $call->getIdentifier());
    }

    public function testToString()
    {
        $call = new Call('foo', 'bar');
        $this->assertSame('foo::bar', (string)$call);
    }

    public function testMatch()
    {
        $trait = GrammarBuilder::create('footrait')
            ->rule('foo')
                ->literal('foo')
            ->getGrammar();
        $grammar = GrammarBuilder::create('foo')
            ->rule('call_foo')
                ->call('footrait', 'foo')
            ->getGrammar();
        $grammar->use($trait);

        $expected = new Node\ExternalReference(
            'footrait',
            'call_foo',
            0, 3,
            new Node\Terminal('foo', 0, 3, 'foo')
        );
        $result = $this->parse($grammar, 'foo');
        $this->assertNodeEquals($expected, $result);
    }

    public function testMatchWithConflictingRuleNames()
    {
        $trait = GrammarBuilder::create('foo')
            ->rule('start')
                ->literal('foo')
            ->getGrammar();
        $grammar = GrammarBuilder::create('foobar')
            ->rule('start')->seq()
                ->call('foo', 'start')
                ->literal('bar')
            ->getGrammar();
        $grammar->use($trait);

        $expected = new Node\Composite('start', 0, 6, [
            new Node\ExternalReference(
                'foo',
                '',
                0,
                3,
                new Node\Terminal('start', 0, 3, 'foo')
            ),
            new Node\Terminal('', 3, 6, 'bar'),
        ]);
        $result = $this->parse($grammar, 'foobar');
        $this->assertNodeEquals($expected, $result);
    }
}
