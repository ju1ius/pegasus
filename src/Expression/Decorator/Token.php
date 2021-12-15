<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Parser\Parser;

final class Token extends Decorator
{
    public function matches(string $text, Parser $parser): Terminal|bool
    {
        $capturing = $parser->isCapturing;
        $parser->isCapturing = false;

        $startPos = $parser->pos;
        $result = $this->children[0]->matches($text, $parser);

        $parser->isCapturing = $capturing;

        return match ($result) {
            true => match ($capturing) {
                true => new Terminal(
                    $this->name,
                    $startPos,
                    $parser->pos,
                    substr($text, $startPos, $parser->pos - $startPos)
                ),
                false => true,
            },
            false => false,
        };
    }

    public function __toString(): string
    {
        return sprintf('%%%s', $this->stringChildren()[0]);
    }
}
