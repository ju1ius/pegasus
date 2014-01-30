<?php

namespace ju1ius\Pegasus\Exception;

use ju1ius\Pegasus\Utils\LineCounter;
use ju1ius\Pegasus\Expression;


/**
 * A call to Expression::parse() or match() didn't match.
 */
class ParseError extends \Exception
{
    public function __construct($text, $pos=0, $expr=null)
    {
        $this->text = $text;
        $this->pos = $pos;
        $this->expr = $expr;
        $this->counter = new LineCounter($this->text);

        parent::__construct();
    }

    public function __toString()
    {
        $rule_name = isset($this->expr->name)
            ? sprintf('"%s"', $this->expr->name)
            : (string) $this->expr
        ;
        return sprintf(
            '%s: rule "%s" didn\'t match on line %s, column %s ("%s").'
            . "\n%s",
            __CLASS__,
            $rule_name,
            $this->counter->line($this->pos),
            $this->counter->column($this->pos) + 1,
            substr($this->text, $this->pos, $this->pos + 20),
            $this->getTraceAsString()
        );
    }

    public function line()
    {
        return 0 === $this->pos
            ? 0
            : substr_count($this->text, "\n", 0, $this->pos) + 1
        ;
    }

    public function column()
    {
        if (0 === $this->pos) return 0;
        $i = strrpos($this->text, "\n", -(strlen($this->text) - $this->pos));
        return false === $i
            ? $this->pos + 1
            : $this->pos - $i
        ;
    }
}
