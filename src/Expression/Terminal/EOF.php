<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Terminal;

use ju1ius\Pegasus\Expression\TerminalExpression;
use ju1ius\Pegasus\Parser\Parser;

/**
 * Matches if there's nothing left to consume (end of input).
 */
final class EOF extends TerminalExpression
{
    public function __construct()
    {
        parent::__construct('EOF');
    }

    public function isCapturing(): bool
    {
        return false;
    }

    public function matches(string $text, Parser $parser): bool
    {
        return !isset($text[$parser->pos]);
    }

    public function __toString(): string
    {
        return 'EOF';
    }
}
