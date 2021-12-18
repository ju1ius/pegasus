<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Decorator;

use ju1ius\Pegasus\Expression\Decorator\Trace;
use ju1ius\Pegasus\Expression\TerminalExpression;
use ju1ius\Pegasus\Parser\Parser;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \ju1ius\Pegasus\Expression\Decorator\Trace
 */
class TraceTest extends TestCase
{
    /**
     * @covers ::matches
     */
    public function testMatch()
    {
        $parser = $this->getMockBuilder(Parser::class)
            ->disableOriginalConstructor()
            ->setMethods(['enterTrace', 'leaveTrace'])
            ->getMockForAbstractClass();
        $child = $this->getMockBuilder(TerminalExpression::class)
            ->setMethods(['matches'])
            ->getMockForAbstractClass();
        $child->method('matches')->willReturn(true);

        $text = 'foo';

        $parser->expects($this->once())
            ->method('enterTrace')
            ->with($child);
        $parser->expects($this->once())
            ->method('leaveTrace')
            ->with($child, true);

        $child->expects($this->once())
            ->method('matches')
            ->with($text, $parser);

        $trace = new Trace($child);
        $trace->matches($text, $parser);
    }

    /**
     * @covers ::__toString
     */
    public function testToString()
    {
        $child = $this->getMockBuilder(TerminalExpression::class)
            ->setMethods(['__toString'])
            ->getMockForAbstractClass();
        $child->method('__toString')->willReturn('foo');
        $trace = new Trace($child);
        Assert::assertSame('foo', (string)$trace);
    }
}
