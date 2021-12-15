<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\CST;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Tests\PegasusTestCase;

class NodeTest extends PegasusTestCase
{
    public function testGetText()
    {
        $node = new class extends Node {
            public int $start = 3;
            public int $end = 6;
        };
        $this->assertSame('bar', $node->getText('foobarbaz'));
    }
}
