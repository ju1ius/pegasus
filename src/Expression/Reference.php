<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Exception\GrammarException;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Parser\ParserInterface;

/**
 * A reference to a named rule, which we must be resolved before matching.
 */
class Reference extends Expression
{
    public $identifier;

    public function __construct($identifier)
    {
        $this->identifier = $identifier;
        parent::__construct();
    }

    public function asRhs()
    {
        return $this->name
            ? "<{$this->name} (reference to {$this->identifier})>"
            : "<{$this->identifier}>";
    }

    public function match($text, $pos, ParserInterface $parser)
    {
        throw new GrammarException(
            sprintf(
                'Unresolved reference to rule <%s>.'
                . ' You must call Grammar::finalize before parsing.',
                $this->identifier
            )
        );
    }
}
