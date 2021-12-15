<?php declare(strict_types=1);
namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Parser\Parser;

/**
 * Expression that skips over what his sub-expression matches.
 * It can dramatically reduce the size of the parse tree.
 */
final class Ignore extends Decorator
{
    public function __toString(): string
    {
        return sprintf('~%s', $this->stringChildren()[0]);
    }

    public function isCapturing(): bool
    {
        return false;
    }

    public function isCapturingDecidable(): bool
    {
        return true;
    }

    public function matches(string $text, Parser $parser): bool
    {
        $capturing = $parser->isCapturing;
        $parser->isCapturing = false;

        $result = $this->children[0]->matches($text, $parser);

        $parser->isCapturing = $capturing;

        return !!$result;
    }
}
