<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Parser\Exception;


/**
 * A call to Parser::parseAll() matched successfully but did not consume the entire text.
 */
class IncompleteParseError extends ParseError
{
    public function __construct(string $text, int $pos)
    {
        parent::__construct($text, $pos);
    }

    public function __toString(): string
    {
        return sprintf(
            "IncompleteParseError: Parsing succeeded without consuming all the input.\n%s",
            $this->sourceExcerpt->getExcerpt($this->position)
        );
    }
}
