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
    public function __construct($text, $pos, ParseError $error)
    {
        parent::__construct($text, $pos, $error->expr, $error->rule);
    }

    public function __toString()
    {
        $ruleName = $this->rule ?: $this->expr->name;
        return sprintf(
            '%s: Expression "%s" in rule "%s" matched entirely without consuming all the input.'
            . PHP_EOL
            . 'Beginning of non-matching portion (line %s, column %s):'
            . PHP_EOL
            . '%s',
            __CLASS__,
            (string)$this->expr,
            $ruleName,
            $this->line(),
            $this->column(),
            substr($this->text, $this->position, 20)
        ) . "\nStack trace:\n" . $this->getTraceAsString();
    }
}
