<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Terminal;

final class HexDigit extends CapturingRegExp
{
    public function __construct(string $name = '')
    {
        parent::__construct('[0-9A-Fa-f]', [], $name);
    }

    public function __toString(): string
    {
        return 'XDIGIT';
    }
}
