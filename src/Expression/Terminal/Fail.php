<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Terminal;

use ju1ius\Pegasus\Expression\TerminalExpression;
use ju1ius\Pegasus\Parser\Parser;

/**
 * An expression that always fail without consuming any input.
 *
 * This can be used to signal malformed input.
 */
final class Fail extends TerminalExpression
{
    public function __toString(): string
    {
        return '#FAIL';
    }

    public function isCapturing(): bool
    {
        return false;
    }

    public function matches(string $text, Parser $parser): bool
    {
        return false;
    }
}
