<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\Expression;

use ju1ius\Pegasus\Expression\Terminal\Fail;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Parser\RecursiveDescent;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class FailTest extends ExpressionTestCase
{
    public function testMatch()
    {
        $parser = new RecursiveDescent(Grammar::fromArray([
            'test' => new Fail()
        ]));
        $this->expectException(ParseError::class);
        $result = $parser->parse('anything', 0);
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
