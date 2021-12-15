<?php declare(strict_types=1);

namespace ju1ius\Pegasus\CST\Node;

class Quantifier extends Composite
{
    public function __construct(
        public string $name,
        public int $start,
        public int $end,
        public array $children = [],
        /** Whether this node is the result of an optional match (? quantifier). */
        public bool $isOptional = false
    ) {
    }
}
