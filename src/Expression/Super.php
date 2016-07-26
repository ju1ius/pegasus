<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Parser\Scope;

/**
 * Applies a rule inherited from a super-grammar.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
final class Super extends Expression
{
    /**
     * The name of the rule this expression refers to.
     *
     * @var string
     */
    private $identifier;

    public function __construct($identifier, $name = '')
    {
        parent::__construct($name);
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier()
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
    public function match($text, Parser $parser)
    {
        $bindings = $parser->bindings;
        $parser->bindings = [];

        $result = $parser->apply($this->identifier, true);

        $parser->bindings = $bindings;

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return sprintf('super::%s', $this->identifier);
    }
}
