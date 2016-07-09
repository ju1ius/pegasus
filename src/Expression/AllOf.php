<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Parser\Scope;

/**
 * A series of expressions, each of which must succeed from the current position.
 *
 * The returned node is the last child, making the sequence of preceding children
 * equivalent to a lookbehind.
 */
class AllOf extends Composite
{
    /**
     * @inheritdoc
     */
    public function match($text, Parser $parser, Scope $scope)
    {
        $start = $parser->pos;
        foreach ($this->children as $child) {
            $node = $child->match($text, $parser, $scope);
            if (!$node) {
                $parser->pos = $start;
                return null;
            }
        }

        return new Node\Decorator($this->name, $start, $parser->pos, $node);
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return sprintf(
            '&<(%s)',
            implode(' ', $this->stringChildren())
        );
    }
}
