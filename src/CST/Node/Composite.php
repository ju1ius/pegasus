<?php declare(strict_types=1);

namespace ju1ius\Pegasus\CST\Node;

use ju1ius\Pegasus\CST\Node;

/**
 * A Node that has child nodes.
 */
class Composite extends Node
{
    public function __construct(
        public string $name,
        public int $start,
        public int $end,
        /** @var Node[] */
        public array $children = [],
    ) {
    }
}
