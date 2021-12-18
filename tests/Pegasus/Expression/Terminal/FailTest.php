<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Expression\Terminal;

use ju1ius\Pegasus\Expression\Terminal\Fail;
use ju1ius\Pegasus\GrammarFactory;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Parser\RecursiveDescentParser;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

class FailTest extends ExpressionTestCase
{
    public function testMatch()
    {
        $parser = new RecursiveDescentParser(GrammarFactory::fromArray([
            'test' => new Fail()
        ]));
        $this->expectException(ParseError::class);
        $result = $parser->partialParse('anything', 0);
        $this->assertSame(null, $result);
        $this->assertSame(0, $parser->pos, 'Does not consume any input.');
    }

    public function testMetadata()
    {
        $expr = new Fail();
        $this->assertTrue($expr->isCapturingDecidable());
        $this->assertFalse($expr->isCapturing());
    }
}
