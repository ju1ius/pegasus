<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Parser\Scope;

/**
 * Wraps an expression in order to give it an unique label.
 *
 * This allows for example to identify an expression in a local context.
 */
final class Label extends Decorator
{
    private $label;

    public function __construct($child, $label)
    {
        parent::__construct($child);
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function __toString()
    {
        return sprintf('%s:%s', $this->label, $this->stringChildren()[0]);
    }

    public function match($text, Parser $parser, Scope $scope)
    {
        $start = $parser->pos;
        if ($result = $this->children[0]->match($text, $parser, $scope)) {
            $scope[$this->label] = substr($text, $start, $parser->pos);

            return $result;
        }
    }
}
