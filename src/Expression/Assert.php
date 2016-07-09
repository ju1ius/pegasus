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
 * Decorates an expression and succeeds or fails like the decorated expression,
 * but never consumes any input (zero-width positive lookahead).
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class Assert extends Decorator
{
    public function __toString()
    {
        return sprintf('&(%s)', $this->stringChildren());
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
        $start = $parser->pos;
        if ($node = $this->children[0]->match($text, $parser, $scope)) {
            $parser->pos = $start;

            return new Node\Transient($start, $start);
        }
    }
}
