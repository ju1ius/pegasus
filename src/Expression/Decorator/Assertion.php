<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\Expression\Decorator;

abstract class Assertion extends Decorator
{
    final public function isCapturing(): bool
    {
        return false;
    }

    final public function isCapturingDecidable(): bool
    {
        return true;
    }
}
