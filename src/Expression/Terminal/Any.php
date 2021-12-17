<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Terminal;

use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Parser\Parser;

final class Any extends RegExp
{
    public function __construct(string $name = '')
    {
        parent::__construct('.', ['s'], $name);
    }

    public function __toString(): string
    {
        return '.';
    }

    public function matches(string $text, Parser $parser): Terminal|bool
    {
        if ($parser->pos < \strlen($text)) {
            $pos = $parser->pos++;
            return match ($parser->isCapturing) {
                true => new Terminal($this->name, $pos, $parser->pos, $text[$pos]),
                false => true,
            };
        }
        return false;
    }
}
