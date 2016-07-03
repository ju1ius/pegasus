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
use ju1ius\Pegasus\Parser\Scope;

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

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->identifier;
    }

    /**
     * @inheritDoc
     */
    public function isCapturingDecidable()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function match($text, $pos, ParserInterface $parser, Scope $scope)
    {
        return $parser->applyRule($this->identifier, $pos, Scope::void());
    }
}
