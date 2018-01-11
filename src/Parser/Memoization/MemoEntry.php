<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Parser\Memoization;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\LeftRecursion;


final class MemoEntry
{
    /**
     * @var LeftRecursion|Node|null
     */
    public $result;

    /**
     * @var int
     */
    public $end;

    /**
     * @var int
     */
    public $key = NAN;

    public function __construct(int $position, $result)
    {
        $this->result = $result;
        $this->end = $position;
    }
}
