<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression\Terminal;

use ju1ius\Pegasus\Expression\Terminal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Parser\Scope;

/**
 * A string literal
 *
 * Use these if you can; they're the fastest.
 */
final class Literal extends Terminal
{
    /**
     * @var string
     */
    private $literal;

    /**
     * @var string
     */
    private $quoteCharacter = '"';

    /**
     * @var int
     */
    private $length = 0;

    public function __construct(string $literal, string $name = '', string $quoteCharacter = '"')
    {
        parent::__construct($name);
        $this->literal = $literal;
        $this->quoteCharacter = $quoteCharacter;

        $this->length = strlen($this->literal);
    }

    public function getLiteral(): string
    {
        return $this->literal;
    }

    public function getQuoteCharacter(): string
    {
        return $this->quoteCharacter;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function __toString(): string
    {
        return sprintf(
            '%1$s%2$s%1$s',
            $this->quoteCharacter,
            addcslashes($this->literal, $this->quoteCharacter)
        );
    }

    public function match(string $text, Parser $parser)
    {
        $start = $parser->pos;
        if (substr($text, $start, $this->length) === $this->literal) {
            $end = $parser->pos += $this->length;
            return $parser->isCapturing
                ? new Node\Terminal($this->name, $start, $end, $this->literal)
                : true;
        }

        $parser->registerFailure($this, $start);
    }
}
