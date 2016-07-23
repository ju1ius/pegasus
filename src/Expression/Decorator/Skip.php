<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Parser\Scope;

/**
 * Expression that skips over what his sub-expression matches.
 *
 * It can dramatically reduce the size of the parse tree.
 */
class Skip extends Decorator
{
    public function __toString()
    {
        return sprintf('~%s', $this->stringChildren()[0]);
    }

    public function isCapturing()
    {
        return false;
    }

    public function isCapturingDecidable()
    {
        return true;
    }

    public function match($text, Parser $parser, Scope $scope)
    {
        $capturing = $parser->isCapturing;
        $parser->isCapturing = false;

        $result = $this->children[0]->match($text, $parser, $scope);

        $parser->isCapturing = $capturing;

        return !!$result;
    }
}
