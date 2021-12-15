<?php declare(strict_types=1);

namespace ju1ius\Pegasus\CST\Node;

use ju1ius\Pegasus\CST\Node;

class ExternalReference extends Node
{
    public function __construct(
        public string $namespace,
        public string $name,
        public int $start,
        public int $end,
        public ?Node $child = null
    ) {
    }
}
