<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Terminal;

final class Any extends NonCapturingRegExp
{
    public function __construct(string $name = '')
    {
        parent::__construct('.', ['s'], $name);
    }

    public function __toString(): string
    {
        return '.';
    }
}
