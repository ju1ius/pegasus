<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Node;


/**
 * A string literal
 *
 * Use these if you can; they're the fastest.
 **/
class Literal extends Terminal
{
	use HasBackReferenceTrait;

    /**
     * @var string
     */
    public $literal;

	/**
	 * @var string
	 */
	public $quotechar;

    /**
     * @var int
     */
    protected $length;


	public function __construct($literal, $name='', $quotechar='"')
    {
        parent::__construct($name);
		$this->literal = $literal;
		$this->quotechar = $quotechar;
        $this->setup();
    }

    public function setup()
    {
		$this->splitSubject($this->literal);
		if (!$this->hasBackReference) {
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

        if ($this->hasBackReference) {
			$value = $this->replaceSubject([$parser, 'getReference']);
            $length = strlen($value);
        }
        if ($pos === strpos($text, $value, $pos)) {
            return new Node\Literal($this, $text, $pos, $pos + $length);
        }
    }
}
