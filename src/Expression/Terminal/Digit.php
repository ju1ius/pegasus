<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Terminal;

final class Digit extends CapturingRegExp
{
    public function __construct(string $name = '')
    {
        parent::__construct('\d', [], $name);
    }

    public function __toString(): string
    {
        return 'DIGIT';
    }
}
