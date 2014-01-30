<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Utils\String;


/**
 * An expression that matches what a regex does.
 *
 * Use these as much as you can and jam as much into each one as you can;
 * they're fast.
 **/
class Regex extends Terminal
{
    /**
     * @var string
     */
    public $pattern;

    /**
     * @var array
     */
    public $flags;

    /**
     * @var string
     */
    public $compiled_pattern;

    /**
     * @var string
     */
    public $compiled_flags;

    /**
     * @var boolean
     */
    public $hasBackReference = false;

    /**
     * @var array
     */
    public $subjectParts = [];

    public function __construct($pattern, $name='', array $flags=[])
    {
        parent::__construct($name);
        $this->pattern = $pattern;
        $this->flags = array_unique(array_merge($flags, ['S', 'x']));
        $this->setup();
    }

    protected function setup()
    {
        $this->compiled_flags = implode('', $this->flags);
        $this->compiled_pattern = sprintf(
            '/\G %s /%s',
            $this->pattern,
            $this->compiled_flags
        );
        // check for backreferences
        $parts = String::splitBackrefSubject($this->pattern);
        if ($parts) {
            $this->hasBackReference = true;
            $this->subjectParts = $parts;
        }
    }

    public function asRhs()
    {
        return $this->compiled_pattern;
    }

    public function match($text, $pos, ParserInterface $parser)
    {
        if ($this->hasBackReference) {
            $pattern = '/\G'
                . String::replaceBackrefSubject($this->subjectParts, [$parser, 'getReference'], true)
                . '/'
                . $this->compiled_flags
            ;
        } else {
            $pattern = $this->compiled_pattern;
        }
        if(preg_match($pattern, $text, $matches, 0, $pos)) {
            $match = $matches[0];
            $length = strlen($match);
            $node = new Node\Regex($this, $text, $pos, $pos + $length, $matches);
            return $node;
        }
    }
}
