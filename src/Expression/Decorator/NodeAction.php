<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Parser\Scope;

/**
 * A sequence that must have a name, used to create "inline" rules.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
final class NodeAction extends Decorator
{
    /**
     * @var string
     */
    private $label;

    /**
     * @inheritDoc
     */
    public function __construct(Expression $child = null, $label)
    {
        $this->label = $label;
        parent::__construct($child, '');
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @inheritDoc
     */
    public function match($text, Parser $parser, Scope $scope)
    {
        $start = $parser->pos;
        if ($result = $this->children[0]->match($text, $parser, $scope)) {
            if (!$parser->isCapturing) {
                return $result;
            }
            if ($result === true) {
                return new Node\Decorator($this->label, $start, $parser->pos);
            }
            if (!$result->name) {
                $result->name = $this->label;

                return $result;
            }

            return new Node\Decorator($this->label, $start, $parser->pos, $result);
        }
        $parser->pos = $start;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        $child = $this->children[0];
        if ($child instanceof OneOf || $child instanceof self) {
            $child = sprintf('(%s)', $child);
        } else {
            $child = (string)$child;
        }

        return sprintf('%s <= %s', $child, $this->label);
    }
}
