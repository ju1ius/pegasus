<?php declare(strict_types=1);

namespace ju1ius\Pegasus\CST\Node;

use ju1ius\Pegasus\CST\Node;

/**
 * A composite node having only one child node.
 */
class Decorator extends Composite
{
    public function __construct(
        public string $name,
        public int $start,
        public int $end,
        public ?Node $child = null
    ) {
        $this->children = $child ? [$child] : [];
    }
}
