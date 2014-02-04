<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Exception;

use ju1ius\Pegasus\Expression;


/**
 * A call to parse() matched a whole Expression but did not consume the entire text.
 */
class IncompleteParseError extends ParseError
{
    public function __toString()
    {
		$rule_name = $this->expr->name ?: (string) $this->expr;
        return sprintf(
            '%s: rule "%s" matched entirely but didn\'t consume all the text. '
            . 'Beginning of non-matching portion (line %s, column %s): "%s".',
            __CLASS__,
            $rule_name,
            $this->line(),
            $this->column(),
            substr($this->text, $this->pos, 20)
        ) . "\nStack trace:\n" . $this->getTraceAsString();
    }
}
