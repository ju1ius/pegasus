<?php

namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\Expression\Decorator;
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
        $capturing = $parser->isCapturing;
        $parser->isCapturing = false;

        $startPos = $parser->pos;
        $result = $this->children[0]->match($text, $parser, $scope);

        $parser->isCapturing = $capturing;
        if ($result) {
            return $capturing
                ? new Node\Terminal(
                    $this->name,
                    $startPos,
                    $parser->pos,
                    substr($text, $startPos, $parser->pos - $startPos)
                )
                : true;
        }
    }

    public function __toString()
    {
        return sprintf('@%s', $this->stringChildren()[0]);
    }
}
