<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Parser;


/**
 * An expression that succeeds whether or not the contained one does.
 *
 * If the contained expression succeeds,
 * it goes ahead and consumes what it consumes.
 * Otherwise, it consumes nothing.
 */
final class Optional extends Quantifier
{
    public function __construct(?Expression $child = null, ?string $name = '')
    {
        parent::__construct($child, 0, 1, $name);
    }

    /**
     * @inheritDoc
     */
    public function isZeroOrMore(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isOneOrMore(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isOptional(): bool
    {
        return true;
    }

    public function match(string $text, Parser $parser)
    {
        $start = $parser->pos;
        $capturing = $parser->isCapturing;

        $result = $this->children[0]->match($text, $parser);

        if ($capturing) {
            return new Node\Quantifier(
                $this->name,
                $start,
                $parser->pos,
                $result ? [$result] : [],
                true
            );
        }

        return true;
    }
}
