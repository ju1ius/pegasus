<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\Expression\Terminal;

use ju1ius\Pegasus\Expression\Terminal\EOF;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Parser\RecursiveDescent;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class EOFTest extends ExpressionTestCase
{
    /**
     * @dataProvider getMatchProvider
     *
     * @param string $input
     * @param int    $pos
     * @param bool   $expected
     */
    public function testMatch($input, $pos, $expected)
    {
        $parser = new RecursiveDescent(Grammar::fromArray([
            'test' => new EOF()
        ]));
        if (!$expected) {
            $this->expectException(ParseError::class);
        }
        $result = $parser->parse($input, $pos);
        $this->assertSame($expected, $result);
        $this->assertSame($pos, $parser->pos, 'Does not consume any input.');
    }

    public function getMatchProvider()
    {
        return [
            'Matches when input is empty.' => [
                '', 0, true,
            ],
            'Matches at the end of the input.' => [
                'foo', 3, true,
            ],
            'Fails if not at end of the input.' => [
                'foo', 0, false,
            ],
        ];
    }

    public function testMetadata()
    {
        $expr = new EOF();
        $this->assertTrue($expr->isCapturingDecidable());
        $this->assertFalse($expr->isCapturing());
    }
}
