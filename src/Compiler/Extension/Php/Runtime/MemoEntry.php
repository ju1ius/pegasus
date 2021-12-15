<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Compiler\Extension\Php\Runtime;

use ju1ius\Pegasus\CST\Node;

final class MemoEntry
{
    public int $end;

    public function __construct(
        public Node|LeftRecursion|null $result,
        int $endPosition
    ) {
        $this->end = $endPosition;
    }
}
