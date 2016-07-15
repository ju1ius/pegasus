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

use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\Scope;

/**
 * Matches if there's nothing left to consume (end of input).
 */
class EOF extends Terminal
{
    public function __construct()
    {
        parent::__construct('EOF');
    }

    public function isCapturing()
    {
        return false;
    }

    public function match($text, Parser $parser, Scope $scope)
    {
        $start = $parser->pos;
        if (!isset($text[$start])) {
            return true;
        }
        if ($start > $parser->error->position) {
            $parser->error->position = $start;
            $parser->error->expr = $this;
        }
    }

    public function __toString()
    {
        return 'EOF';
    }
}
