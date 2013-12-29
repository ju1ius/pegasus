<?php

namespace ju1ius\Pegasus\Exception;


/**
 * A call to parse() matched a whole Expression but did not consume the entire text.
 */
class IncompleteParseError extends ParseError
{
    public function __toString()
    {
        $rule_name = isset($this->expr->name)
            ? sprintf('"%s"', $this->expr->name)
            : (string) $this->expr
        ;
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
