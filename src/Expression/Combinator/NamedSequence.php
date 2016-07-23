<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression\Combinator;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Combinator;
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Parser\Scope;

/**
 * A sequence that must have a name, used to create "inline" rules.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
final class NamedSequence extends Combinator
{
    private $label;

    /**
     * @inheritDoc
     */
    public function __construct(array $children, $label)
    {
        $this->label = $label;
        parent::__construct($children, '');
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
        $startPos = $parser->pos;
        $capturing = $parser->isCapturing;
        $children = $capturing ? [] : null;
        foreach ($this->children as $child) {
            $result = $child->match($text, $parser, $scope);
            if (!$result) {
                $parser->pos = $startPos;
                return null;
            }
            if ($result === true || !$capturing) {
                continue;
            }
            $children[] = $result;
        }

        return $capturing
            ? new Node\Composite($this->label, $startPos, $parser->pos, $children)
            : true;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return sprintf(
            '%s <= %s',
            implode(' ', $this->stringChildren()),
            $this->label
        );
    }

    /**
     * @inheritDoc
     */
    protected function stringChildren()
    {
        return array_map(function (Expression $child) {
            if ($child instanceof OneOf || $child instanceof NamedSequence) {
                return sprintf('(%s)', $child);
            }

            return (string)$child;
        }, $this->children);
    }
}
