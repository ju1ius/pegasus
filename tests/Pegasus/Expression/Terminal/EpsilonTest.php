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

use ju1ius\Pegasus\Expression\Terminal\Epsilon;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Parser\RecursiveDescent;
use ju1ius\Pegasus\Tests\ExpressionTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class EpsilonTest extends ExpressionTestCase
{
    /**
     * @dataProvider getMatchProvider
     *
     * @param string $input
     * @param int    $pos
     * @param bool   $expected
     *
     * @throws Grammar\Exception\AnonymousTopLevelExpression
     */
    public function testMatch($input, $pos, $expected)
    {
        $parser = new RecursiveDescent(Grammar::fromArray([
            'test' => new Epsilon()
        ]));
        if (!$expected) {
            $this->expectException(ParseError::class);
        }
        $result = $parser->partialParse($input, $pos);
        $this->assertSame($expected, $result);
        $this->assertSame($pos, $parser->pos, 'Does not consume any input.');
    }

    public function getMatchProvider()
    {
        return [
            'Matches when input is empty.' => [
                '', 0, true,
            ],
            'Matches anything without consuming any input.' => [
                'foo', 1, true,
            ],
        ];
    }

    public function testMetadata()
    {
        $expr = new Epsilon();
        $this->assertTrue($expr->isCapturingDecidable());
        $this->assertFalse($expr->isCapturing());
    }
}
