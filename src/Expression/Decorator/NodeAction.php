<?php declare(strict_types=1);
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
    public function __construct(Expression $child = null, string $label)
    {
        $this->label = $label;
        parent::__construct($child, '');
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @inheritDoc
     */
    public function match(string $text, Parser $parser)
    {
        $start = $parser->pos;
        if ($result = $this->children[0]->match($text, $parser)) {
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
    public function __toString(): string
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
