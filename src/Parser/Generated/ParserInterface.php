<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Parser\Generated;

use ju1ius\Pegasus\Parser\Exception\IncompleteParseError;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Node;

interface ParserInterface
{
    /**
     * Return the parse tree matching this expression at the given position,
     * not necessarily extending all the way to the end of $text.
     *
     * @param string $text
     * @param string $startRule
     *
     * @return Node
     *
     * @throws ParseError If the text doesn't match
     * @throws IncompleteParseError If the text doesn't match entirely
     */
    public function parseAll($text, $startRule = null);

    /**
     * Parse the given text starting from given position, using the given start rule.
     *
     * @param string $text
     * @param int    $position
     * @param string $startRule
     *
     * @return Node
     *
     * @throws ParseError If the text doesn't match
     */
    public function parse($text, $position = 0, $startRule = null);
}
