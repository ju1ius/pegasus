<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Parser\Scope;

/**
 * @author ju1ius
 */
class Token extends Decorator
{
    /**
     * @inheritDoc
     */
    public function match($text, Parser $parser, Scope $scope)
    {
        $startPos = $parser->pos;
        if ($node = $this->children[0]->match($text, $parser, $scope)) {
            return new Node\Terminal(
                $this->name,
                $startPos,
                $parser->pos,
                substr($text, $startPos, $parser->pos - $startPos)
            );
        }
    }

    public function __toString()
    {
        return sprintf('@(%s)', $this->children[0]->__toString());
    }
}
