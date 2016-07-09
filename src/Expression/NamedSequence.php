<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Parser\Scope;

/**
 * A sequence that must have a name, used to create "inline" rules.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class NamedSequence extends Composite
{
    public $label;

    /**
     * @inheritDoc
     */
    public function __construct(array $children, $label)
    {
        $this->label = $label;
        parent::__construct($children, '');
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return sprintf(
            '%s <= %s',
            implode(' ', $this->stringChildren()),
            $this->label
        );
    }

    /**
     * @inheritDoc
     */
    public function match($text, $pos, ParserInterface $parser, Scope $scope)
    {
        $nextPos = $pos;
        $children = [];
        foreach ($this->children as $child) {
            $node = $parser->apply($child, $nextPos, $scope);
            if (!$node) {
                return null;
            }
            $length = $node->end - $node->start;
            $nextPos += $length;
            if ($node->isTransient) {
                continue;
            }
            $children[] = $node;
        }

        return new Node\Composite($this->label, $pos, $nextPos, $children);
    }
}
