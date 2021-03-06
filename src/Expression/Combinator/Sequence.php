<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression\Combinator;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Combinator;
use ju1ius\Pegasus\Expression\Decorator\NodeAction;
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Parser;

/**
 * A series of expressions that must match contiguous, ordered pieces of the text.
 *
 * In other words, it's a concatenation operator: each piece has to match, one after another.
 */
final class Sequence extends Combinator
{
    /**
     * @return int
     */
    public function getCaptureCount(): int
    {
        $n = 0;
        foreach ($this->children as $child) {
            if ($child->isCapturing()) $n++;
        }

        return $n;
    }

    public function match(string $text, Parser $parser)
    {
        $startPos = $parser->pos;
        $capturing = $parser->isCapturing;
        $children = $capturing ? [] : null;
        $captureCount = 0;
        foreach ($this->children as $child) {
            $result = $child->match($text, $parser);
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

    /**
     * @inheritDoc
     */
    protected function stringChildren()
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
