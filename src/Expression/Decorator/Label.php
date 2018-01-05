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
    /**
     * @var string
     */
    private $label;

    public function __construct(Expression $child = null, string $label)
    {
        parent::__construct($child);
        $this->label = $label;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function __toString(): string
    {
        return sprintf('%s:%s', $this->label, $this->stringChildren()[0]);
    }

    public function match(string $text, Parser $parser)
    {
        $start = $parser->pos;
        if ($result = $this->children[0]->match($text, $parser)) {
            $parser->bindings[$this->label] = substr($text, $start, $parser->pos - $start);

            return $result;
        }
    }
}
