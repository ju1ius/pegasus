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

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Combinator;
use ju1ius\Pegasus\Parser\Parser;

/**
 * A series of expressions, one of which must match
 *
 * Expressions are tested in order from first to last.
 * The first to succeed wins.
 */
final class OneOf extends Combinator
{
    public function isCapturingDecidable(): bool
    {
        $capturingChildren = 0;
        foreach ($this->children as $child) {
            if (!$child->isCapturingDecidable()) {
                return false;
            }
            if ($child->isCapturing()) {
                $capturingChildren++;
            }
        }

        return !$capturingChildren || $capturingChildren === count($this->children);
    }

    public function match(string $text, Parser $parser)
    {
        $start = $parser->pos;
        $capturing = $parser->isCapturing;
        $result = null;
        $parser->cutStack->push(false);

        foreach ($this->children as $child) {
            $result = $child->match($text, $parser);
            if ($parser->cutStack->top()) {
                //TODO: should we backtrack in case of failure ?
                break;
            }
            if (!$result) {
                $parser->pos = $start;
                continue;
            }
            break;
        }
        $parser->cutStack->pop();

        if (!$result) {
            return false;
        }
        if (!$capturing || $result === true) {
            return true;
        }
        // [CST decimation] Try to skip one tree level if:
        if (!$this->name) {
            // this expression is not a grammar rule, so we can safely
            return $result;
        } elseif (!$result->name) {
            // this expression is a grammar rule but the matching child is not,
            // masquerade the node and return it
            $result->name = $this->name;

            return $result;
        }

        return new Node\Decorator($this->name, $start, $parser->pos, $result);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return implode(' | ', $this->stringChildren());
    }

    /**
     * @inheritDoc
     */
    protected function stringChildren()
    {
        return array_map(function (Expression $child) {
            return $child instanceof OneOf ? sprintf('(%s)', $child) : (string)$child;
        }, $this->children);
    }
}
