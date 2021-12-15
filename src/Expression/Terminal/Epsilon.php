<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Terminal;

use ju1ius\Pegasus\Expression\TerminalExpression;
use ju1ius\Pegasus\Parser\Parser;

/**
 * The empty string
 *
 * Always matches without consuming any input.
 */
final class Epsilon extends TerminalExpression
{
    public function __toString(): string
    {
        return 'ε';
    }

    public function isCapturing(): bool
    {
        return false;
    }

    public function matches(string $text, Parser $parser): bool
    {
        return true;
    }
}
