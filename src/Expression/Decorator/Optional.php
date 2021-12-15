<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\CST\Node\Quantifier as QuantifierNode;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Parser\Parser;

/**
 * A composite expression that succeeds even when the contained one fails.
 * If the contained expression succeeds, it goes ahead and consumes what it consumes.
 * Otherwise, it consumes nothing.
 */
final class Optional extends Quantifier
{
    public function __construct(?Expression $child = null, ?string $name = '')
    {
        parent::__construct($child, 0, 1, $name);
    }

    public function isZeroOrMore(): bool
    {
        return false;
    }

    public function isOneOrMore(): bool
    {
        return false;
    }

    public function isOptional(): bool
    {
        return true;
    }

    public function matches(string $text, Parser $parser): QuantifierNode|bool
    {
        $start = $parser->pos;
        $capturing = $parser->isCapturing;

        $result = $this->children[0]->matches($text, $parser);

        if ($capturing) {
            return new QuantifierNode(
                $this->name,
                $start,
                $parser->pos,
                ($result && $result !== true) ? [$result] : [],
                true
            );
        }

        return true;
    }
}
