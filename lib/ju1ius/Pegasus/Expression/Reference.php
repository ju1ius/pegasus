<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Exception\GrammarException;


/**
 * A reference to a named rule,
 * which we resolve after grokking all the rules.
 */
class Reference extends Expression
{
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
        parent::__construct();
    }
    
    public function asRhs()
    {
        return "<Reference to {$this->identifier}>";
    }

    public function match($text, $pos, ParserInterface $parser)
    {
		throw new GrammarException(sprintf(
			"Unresolved reference to rule '%s'. "
			. "You must call Grammar::finalize before parsing.",
			$this->identifier
		));
    }
}
