<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Exception\GrammarException;


/**
 * A reference to a named rule,
 * which we must be resolved before matching.
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
        return $this->name
            ? "<{$this->name} (reference to {$this->identifier})>"
            : "<Reference to {$this->identifier}>";
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
