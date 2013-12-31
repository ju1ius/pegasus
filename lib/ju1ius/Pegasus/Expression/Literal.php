<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Parser\ParserInterface;
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

    protected $has_ref;

    const BACKREF_RX = '/\$\{(\w+)\}/';

    public function __construct($literal, $name='')
    {
        parent::__construct($name);
        $this->literal = $literal;
        $this->setup();
    }

    public function setup()
    {
        $this->has_ref = preg_match(self::BACKREF_RX, $this->literal);
        if (!$this->has_ref) {
            $this->length = strlen($this->literal);
        }
    }

    public function asRhs()
    {
        //TODO backslash escaping
        return sprintf('"%s"', $this->literal);
    }

    public function match($text, $pos, ParserInterface $parser)
    {
        $value = $this->literal;
        $length = $this->length;

        if ($this->has_ref) {
            $value = preg_replace_callback(self::BACKREF_RX, function($matches) use($parser) {
                $result = $parser->getReference($matches[1]);
                return (string) $result;
            }, $this->literal);
            $length = strlen($value);
        }
        if ($pos === strpos($text, $value, $pos)) {
            return new Node($this->name, $text, $pos, $pos + $length);
        }
    }
}
