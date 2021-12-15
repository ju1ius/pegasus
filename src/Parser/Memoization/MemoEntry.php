<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Parser\Memoization;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\LeftRecursion;

final class MemoEntry
{
    /**
     * @var LeftRecursion|Node|null
     */
    public $result;

    public int $end;

    /**
     * @var int
     */
    public $hash = NAN;

    public function __construct(int $position, $result)
    {
        $this->result = $result;
        $this->end = $position;
    }
}
