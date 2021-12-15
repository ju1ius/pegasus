<?php declare(strict_types=1);

namespace ju1ius\Pegasus\CST\Node;

use ju1ius\Pegasus\CST\Node;

class Invalid extends Node
{
    public function __construct(
        public ?string $value = null
    ) {
    }
}
