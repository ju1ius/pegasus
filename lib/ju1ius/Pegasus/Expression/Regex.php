<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Node;


/**
 * An expression that matches what a regex does.
 *
 * Use these as much as you can and jam as much into each one as you can;
 * they're fast.
 **/
class Regex extends Terminal
{
    public $pattern;
    protected $compiled_pattern;

    public function __construct($pattern, $name='', array $flags=[])
    {
        parent::__construct($name);
        $this->pattern = $pattern;
        $this->flags = array_unique(array_merge($flags, ['S', 'x']));
        $this->compiled_pattern = sprintf(
            '/\G%s/%s',
            $this->pattern,
            implode('', $this->flags)
        );
    }

    public function asRhs()
    {
        return $this->compiled_pattern;
    }

    public function match($text, $pos, ParserInterface $parser)
    {
        if(preg_match($this->compiled_pattern, $text, $matches, 0, $pos)) {
            $match = $matches[0];
            $length = strlen($match);
            $node = new Node\Regex($this, $text, $pos, $pos + $length, $matches);
            return $node;
        }
    }
}
