<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Parser\Parser;

/**
 * Wraps an expression in order to bind it to an unique name.
 * This allows for example to identify an expression in a local context.
 */
final class Bind extends Decorator
{
    public function __construct(
        private string $label, ?Expression $child = null)
    {
        parent::__construct($child);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function __toString(): string
    {
        return sprintf('%s:%s', $this->label, $this->stringChildren()[0]);
    }

    public function matches(string $text, Parser $parser): Node|bool
    {
        $start = $parser->pos;
        if ($result = $this->children[0]->matches($text, $parser)) {
            $value = match ($result) {
                true => substr($text, $start, $parser->pos - $start),
                default => substr($text, $result->start, $result->end - $result->start),
            };
            $parser->bind($this->label, $value);
            return $result;
        }
        return false;
    }
}
