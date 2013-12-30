<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Exception\ParseError;
use ju1ius\Pegasus\Node\Regex as RegexNode;

/**
 * An expression that matches what a regex does.
 *
 * Use these as much as you can and jam as much into each one as you can;
 * they're fast.
 **/
class Regex extends Expression
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

    public function match($text, $pos, $parser)
    {
        // @TODO: should we use PREG_OFFSET_CAPTURE or substr($text, $pos) ?
        if(preg_match($this->compiled_pattern, $text, $matches, 0, $pos)) {
            $match = $matches[0];
            $length = strlen($match);
            $node = new RegexNode($this->name, $text, $pos, $pos + $length, [], $matches);
            return $node;
        }
    }
}
