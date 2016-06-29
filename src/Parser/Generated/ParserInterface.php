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

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Node;

interface ParserInterface
{
    /**
     * Parse $text starting from position $pos, using start rule $start_rule.
     *
     * @param string $text
     * @param int    $pos
     * @param string $startRule
     *
     * @return Node|null
     */
    public function parse($text, $pos = 0, $startRule = null);

    /**
     * Applies $rule_name at position $pos.
     *
     *
     * @param string $ruleName
     * @param int    $pos
     *
     * @return Node|null
     */
    public function apply($ruleName, $pos);
}
