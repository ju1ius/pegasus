<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\CST;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Tests\PegasusTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class NodeTest extends PegasusTestCase
{
    public function testGetText()
    {
        $input = 'foobarbaz';
        $node = new Node('test', 3, 6, 'bar');
        $this->assertSame('bar', $node->getText($input));
    }
}
