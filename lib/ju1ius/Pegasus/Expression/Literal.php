<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Exception\ParseError;
use ju1ius\Pegasus\Node;


/**
 * A string literal
 *
 * Use these if you can; they're the fastest.
 **/
class Literal extends Expression
{
    /**
     * @var string
     */
    public $literal;
    /**
     * @var int
     */
    protected $length;

    public function __construct($literal, $name='')
    {
        parent::__construct($name);
        $this->literal = $literal;
        $this->length = strlen($literal);
    }

    public function asRhs()
    {
        //TODO backslash escaping
        return sprintf('"%s"', $this->literal);
    }

    public function match($text, $pos, $parser)
    {
        if ($pos === strpos($text, $this->literal, $pos)) {
            return new Node($this->name, $text, $pos, $pos + $this->length);
        }
    }
}
