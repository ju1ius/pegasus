<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\Utils;

use ju1ius\Pegasus\Utils\SourceExcerpt;
use PHPUnit\Framework\TestCase;


/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class SourceExcerptTest extends TestCase
{
    public function testExcerptWithShortLines()
    {
        $source = "123\n456\n789\nABC\nDEF";
        $excerpt = new SourceExcerpt($source, 2, 80);

        $result = $excerpt->getExcerpt(strpos($source, '1'));
        $expected = <<<'EOS'
Line 1, column 1:
   1│ 123
────┴╌┘
EOS;
        $this->assertSame($expected, $result);

        $result = $excerpt->getExcerpt(strpos($source, 'C'));
        $expected = <<<'EOS'
Line 4, column 3:
   …│  …
   3│ 789
   4│ ABC
────┴╌╌╌┘
EOS;
        $this->assertSame($expected, $result);
    }

    public function testExcerptWithLongLines()
    {
        $source = "123456789\nABCDEF123";
        $excerpt = new SourceExcerpt($source, 2, 8 + 6);

        $result = $excerpt->getExcerpt(strpos($source, 'E'));
        $expected = <<<'EOS'
Line 2, column 5:
   1│ 123456 …
   2│ ABCDEF …
────┴╌╌╌╌╌┘
EOS;
        $this->assertSame($expected, $result);

        $result = $excerpt->getExcerpt(strlen($source) - 1);
        $expected = <<<'EOS'
Line 2, column 9:
   1│ 123456 …
   2│ … CDEF123
────┴╌╌╌╌╌╌╌╌╌┘
EOS;
        $this->assertSame($expected, $result);
    }
}
