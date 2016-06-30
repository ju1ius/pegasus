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
 * Wraps an expression in order to give it an unique label.
 *
 * This allows for example to identify an expression in a local context.
 */
class Label extends Decorator
{
    public $label;

    public function __construct($children, $label)
    {
        parent::__construct($children);
        $this->label = $label;
    }

    public function asRightHandSide()
    {
        return sprintf('%s:(%s)', $this->label, $this->stringMembers());
    }

    public function isCapturing()
    {
        return $this->children[0]->isCapturing();
    }

    public function isCapturingDecidable()
    {
        return $this->children[0]->isCapturingDecidable();
    }

    public function match($text, $pos, ParserInterface $parser)
    {
        $node = $parser->apply($this->children[0], $pos);
        if ($node) {
            return new Node\Label($this, $text, $node->start, $node->end, [$node]);
        }
    }
}
