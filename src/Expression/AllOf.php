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
use ju1ius\Pegasus\Parser\ParserInterface;

/**
 * A series of expressions, each of which must succeed from the current position.
 *
 * The returned node is from the last child.
 * If you like, you can think of the preceding children as lookaheads.
 **/
class AllOf extends Composite
{
    /**
     * @inheritdoc
     */
    public function match($text, $pos, ParserInterface $parser)
    {
        foreach ($this->children as $child) {
            $node = $parser->apply($child, $pos);
            if (!$node) {
                return null;
            }
        }

        return new Node\AllOf($this, $text, $pos, $node->end, [$node]);
    }

    /**
     * @inheritdoc
     */
    public function asRhs()
    {
        return implode(' ', $this->stringMembers());
    }
}
