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

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar\Exception\UnresolvedReference;
use ju1ius\Pegasus\Parser\ParserInterface;

/**
 * A reference to a named expression.
 *
 * References must be resolved before matching by calling Grammar::finalize().
 */
class Reference extends Expression
{
    /**
     * The name of the rule this expression refers to.
     *
     * @var string
     */
    public $identifier;

    public function __construct($identifier, $name = '')
    {
        parent::__construct($name);
        $this->identifier = $identifier;
    }

    public function asRightHandSide()
    {
        return $this->identifier;
    }

    public function match($text, $pos, ParserInterface $parser)
    {
        throw new UnresolvedReference($this->identifier);
    }
}
