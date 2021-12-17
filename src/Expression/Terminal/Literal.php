<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Terminal;

use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Expression\TerminalExpression;
use ju1ius\Pegasus\Parser\Parser;

/**
 * A string literal
 *
 * Use these if you can; they're the fastest.
 */
final class Literal extends TerminalExpression
{
    private int $length;

    public function __construct(
        private string $literal,
        string $name = '',
        private string $quoteCharacter = '"'
    ) {
        parent::__construct($name);
        $this->length = \strlen($this->literal);
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

    public function matches(string $text, Parser $parser): Terminal|bool
    {
        $start = $parser->pos;
        if (substr_compare($text, $this->literal, $start, $this->length) === 0) {
            $end = $parser->pos += $this->length;
            return $parser->isCapturing
                ? new Terminal($this->name, $start, $end, $this->literal)
                : true;
        }
        return false;
    }
}
