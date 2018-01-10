<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Decorator;

use ju1ius\Pegasus\Expression\Decorator\Cut;
use ju1ius\Pegasus\Expression\Terminal;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Tests\ExpressionTestCase;


/**
 * @coversDefaultClass \ju1ius\Pegasus\Expression\Decorator\Cut
 */
class CutTest extends ExpressionTestCase
{
    /**
     * @covers ::match
     */
    public function testItCallsParserCut()
    {
        $parser = $this->getMockBuilder(Parser::class)
            ->disableOriginalConstructor()
            ->setMethods(['cut'])
            ->getMockForAbstractClass();
        $child = $this->getMockBuilder(Terminal::class)
            ->disableOriginalConstructor()
            ->setMethods(['match'])
            ->getMockForAbstractClass();
        $child->method('match')->willReturn(true);

        $text = 'foo';
        $parser->expects($this->once())
            ->method('cut')
            ->with(0);
        $child->expects($this->once())
            ->method('match')
            ->with($text, $parser);

        $cut = new Cut($child);

        $cut->match($text, $parser);
    }

    /**
     * @covers ::match
     * @covers \ju1ius\Pegasus\Expression\Combinator\OneOf::match
     */
    public function testItMakesChoiceFail()
    {
        $b = GrammarBuilder::create();
        $grammar = $b->rule('start')->oneOf()
            ->seq()
                ->cut()->literal('!')
                ->literal('foo')
            ->end()
            ->seq()
                ->literal('!')
                ->literal('bar')
            ->getGrammar();

        $this->expectException(ParseError::class);
        $this->parse($grammar, '!bar');
    }

    /**
     * @covers ::__toString
     */
    public function testToString()
    {
        $child = $this->getMockBuilder(Terminal::class)
            ->disableOriginalConstructor()
            ->setMethods(['__toString'])
            ->getMockForAbstractClass();
        $child->method('__toString')
            ->willReturn('foo');

        $cut = new Cut($child);
        $this->assertSame('foo^', (string)$cut);
    }
}
