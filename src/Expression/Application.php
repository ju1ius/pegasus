<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;

/**
 * Base class for expressions that are applications of grammar rules,
 * e.g. Reference, Super, etc...
 */
abstract class Application extends Expression
{
    public function isCapturingDecidable(): bool
    {
        return false;
    }
}
