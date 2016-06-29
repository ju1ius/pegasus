<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Parser;

use ju1ius\Pegasus\Node;

class MemoEntry
{
    /**
     * @var LR|Node|null
     */
    public $result;

    /**
     * @var int
     */
    public $end;

    public function __construct($result, $endPosition)
    {
        $this->result = $result;
        $this->end = $endPosition;
    }
}
