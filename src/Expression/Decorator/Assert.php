<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\Parser\Parser;


/**
 * Decorates an expression and succeeds or fails like the decorated expression,
 * but never consumes any input (zero-width positive lookahead).
 */
final class Assert extends Assertion
{
    public function __toString(): string
    {
        return sprintf('&:%s', $this->stringChildren()[0]);
    }

    public function matches(string $text, Parser $parser): bool
    {
        $start = $parser->pos;
        $capturing = $parser->isCapturing;

        $parser->isCapturing = false;
        $result = $this->children[0]->matches($text, $parser);

        $parser->isCapturing = $capturing;
        $parser->pos = $start;

        return !!$result;
    }
}
