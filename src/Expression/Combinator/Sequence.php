<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Combinator;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Combinator;
use ju1ius\Pegasus\Expression\Decorator\NodeAction;
use ju1ius\Pegasus\Parser\Parser;

/**
 * A series of expressions that must match contiguous, ordered pieces of the text.
 * In other words, it's a concatenation operator: each piece has to match, one after another.
 */
final class Sequence extends Combinator
{
    public function getCaptureCount(): int
    {
        $n = 0;
        foreach ($this->children as $child) {
            if ($child->isCapturing()) $n++;
        }

        return $n;
    }

    public function matches(string $text, Parser $parser): Node|bool
    {
        $startPos = $parser->pos;
        $capturing = $parser->isCapturing;
        $children = $capturing ? [] : null;
        $captureCount = 0;
        foreach ($this->children as $child) {
            $result = $child->matches($text, $parser);
            if (!$result) {
                $parser->pos = $startPos;
                return false;
            }
            if (!$capturing || $result === true) {
                continue;
            }
            $children[] = $result;
            $captureCount++;
        }

        if (!$capturing) {
            return true;
        }
        switch ($captureCount) {
            case 0:
                return true;
            case 1:
                $child = $children[0];
                // [CST decimation] Try to skip one tree level if:
                if (!$this->name) {
                    // this expression is not a grammar rule, so we can safely
                    return $child;
                } elseif (!$child->name) {
                    // this expression is a grammar rule but the matching child is not.
                    // Masquerade the child as ourselves and return it.
                    $child->name = $this->name;
                    return $child;
                }

                return new Node\Decorator($this->name, $startPos, $parser->pos, $child);
            default:
                return new Node\Composite($this->name, $startPos, $parser->pos, $children);
        }
    }

    protected function stringChildren(): array
    {
        return array_map(function (Expression $child) {
            if ($child instanceof OneOf || $child instanceof NodeAction) {
                return sprintf('(%s)', $child);
            }

            return (string)$child;
        }, $this->children);
    }

    public function __toString(): string
    {
        return implode(' ', $this->stringChildren());
    }
}
