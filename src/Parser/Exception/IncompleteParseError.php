<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Parser\Exception;

use ju1ius\Pegasus\Expression;

/**
 * A call to Parser::parseAll() matched successfully but did not consume the entire text.
 */
class IncompleteParseError extends ParseError
{
    public function __construct($text, $pos)
    {
        parent::__construct($text, $pos);
    }

    public function __toString()
    {
        return sprintf(
            "IncompleteParseError: Parsing succeeded without consuming all the input.\n%s",
            $this->sourceExcerpt->getExcerpt($this->position)
        );
    }
}
