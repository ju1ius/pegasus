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
use ju1ius\Pegasus\Parser\Parser;
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
    public function match($text, Parser $parser, Scope $scope)
    {
        $startPos = $parser->pos;
        $children = [];
        foreach ($this->children as $child) {
            $node = $child->match($text, $parser, $scope);
            if (!$node) {
                $parser->pos = $startPos;
                return null;
            }
            if ($node->isTransient) {
                continue;
            }
            $children[] = $node;
        }

        return new Node\Composite($this->label, $startPos, $parser->pos, $children);
    }
}
