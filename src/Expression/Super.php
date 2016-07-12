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
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Parser\Scope;

/**
 * Applyies the rule of the same name inherited from a super-grammar.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class Super extends Expression
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
    public function match($text, Parser $parser, Scope $scope)
    {
        return $parser->apply($this->identifier, $scope, true);
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return sprintf('super::%s', $this->identifier);
    }

    /**
     * @inheritDoc
     */
    public function isCapturingDecidable()
    {
        return false;
    }
}
