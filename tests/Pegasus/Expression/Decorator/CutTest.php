<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Decorator;

use ju1ius\Pegasus\Expression\Decorator\Cut;
use ju1ius\Pegasus\Expression\TerminalExpression;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Tests\ExpressionTestCase;
use PHPUnit\Framework\Assert;

/**
 * @coversDefaultClass \ju1ius\Pegasus\Expression\Decorator\Cut
 */
class CutTest extends ExpressionTestCase
{
    /**
     * @covers ::matches
     */
    public function testItCallsParserCut()
    {
        $parser = $this->getMockBuilder(Parser::class)
            ->disableOriginalConstructor()
            ->setMethods(['cut'])
            ->getMockForAbstractClass();
        $child = $this->getMockBuilder(TerminalExpression::class)
            ->disableOriginalConstructor()
            ->setMethods(['matches'])
            ->getMockForAbstractClass();
        $child->method('matches')->willReturn(true);

        $text = 'foo';
        $parser->expects($this->once())
            ->method('cut')
            ->with(0);
        $child->expects($this->once())
            ->method('matches')
            ->with($text, $parser);

        $cut = new Cut($child);

        $cut->matches($text, $parser);
    }

    /**
     * @covers ::matches
     * @covers \ju1ius\Pegasus\Expression\Combinator\OneOf::matches
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
        self::parse($grammar, '!bar');
    }

    /**
     * @covers ::__toString
     */
    public function testToString()
    {
        $child = $this->getMockBuilder(TerminalExpression::class)
            ->disableOriginalConstructor()
            ->setMethods(['__toString'])
            ->getMockForAbstractClass();
        $child->method('__toString')
            ->willReturn('foo');

        $cut = new Cut($child);
        Assert::assertSame('foo^', (string)$cut);
    }
}
