<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Decorator;

use ju1ius\Pegasus\Expression\Decorator\Trace;
use ju1ius\Pegasus\Expression\Terminal;
use ju1ius\Pegasus\Parser\Parser;
use PHPUnit\Framework\TestCase;


/**
 * @coversDefaultClass \ju1ius\Pegasus\Expression\Decorator\Trace
 */
class TraceTest extends TestCase
{
    /**
     * @covers ::match
     */
    public function testMatch()
    {
        $parser = $this->getMockBuilder(Parser::class)
            ->disableOriginalConstructor()
            ->setMethods(['enterTrace', 'leaveTrace'])
            ->getMockForAbstractClass();
        $child = $this->getMockBuilder(Terminal::class)
            ->disableOriginalConstructor()
            ->setMethods(['match'])
            ->getMockForAbstractClass();
        $child->method('match')->willReturn(true);

        $text = 'foo';

        $parser->expects($this->once())
            ->method('enterTrace')
            ->with($child);
        $parser->expects($this->once())
            ->method('leaveTrace')
            ->with($child, true);

        $child->expects($this->once())
            ->method('match')
            ->with($text, $parser);

        $trace = new Trace($child);
        $trace->match($text, $parser);
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
        $child->method('__toString')->willReturn('foo');
        $trace = new Trace($child);
        $this->assertSame('foo', (string)$trace);
    }
}
