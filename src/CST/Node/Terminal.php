<?php declare(strict_types=1);

namespace ju1ius\Pegasus\CST\Node;

use ju1ius\Pegasus\CST\Node;

class Terminal extends Node
{
    public function __construct(
        public string $name,
        public int $start,
        public int $end,
        public ?string $value = null,
        public array $attributes = []
    ) {
    }
}
