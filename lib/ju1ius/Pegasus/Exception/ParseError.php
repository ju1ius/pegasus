<?php

namespace ju1ius\Pegasus\Exception;


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

        parent::__construct();
    }

    public function __toString()
    {
        $rule_name = isset($this->expr->name)
            ? sprintf('"%s"', $this->expr->name)
            : (string) $this->expr
        ;
        return sprintf(
            '%s: rule "%s" didn\'t match on line %s, column %s ("%s").',
            __CLASS__,
            $rule_name,
            $this->line(),
            $this->column(),
            substr($this->text, $this->pos, $this->pos + 20)
        );
    }

    public function line()
    {
        return substr_count($this->text, "\n", 0, $this->pos) + 1;
    }

    public function column()
    {
        $i = strrpos($this->text, "\n", -(strlen($this->text) - $this->pos));
        if (false === $i) return $this->pos + 1;
        return $this->pos - $i;
    }
}
