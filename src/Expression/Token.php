<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Parser\Scope;

/**
 * @author ju1ius
 */
class Token extends Decorator
{
    public function match($text, $pos, ParserInterface $parser, Scope $scope)
    {
        if ($node = $parser->apply($this->children[0], $pos, $scope)) {
            return new Node($this->name, $node->start, $node->end, $text);
        }
    }

    public function __toString()
    {
        return sprintf('@(%s)', $this->children[0]->__toString());
    }
}
