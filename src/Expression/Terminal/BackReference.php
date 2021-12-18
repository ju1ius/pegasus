<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Terminal;

use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Expression\TerminalExpression;
use ju1ius\Pegasus\Parser\Exception\UndefinedBinding;
use ju1ius\Pegasus\Parser\Parser;

final class BackReference extends TerminalExpression
{
    public function __construct(
        private string $identifier,
        string $name = ''
    ) {
        parent::__construct($name);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @todo This will always match if the bound value is the empty string.
     * We should check if that's expected behaviour.
     */
    public function matches(string $text, Parser $parser): Terminal|bool
    {
        if (null === $pattern = $parser->scope->bindings[$this->identifier] ?? null) {
            throw new UndefinedBinding($this->identifier, $parser->scope);
        }

        $start = $parser->pos;
        $length = \strlen($pattern);

        if (substr_compare($text, $pattern, $start, $length) === 0) {
            $end = $parser->pos += $length;
            return match ($parser->isCapturing) {
                true => new Terminal($this->name, $start, $end, $pattern),
                false => true,
            };
        }
        return false;
    }

    public function __toString(): string
    {
        return sprintf('$%s', $this->identifier);
    }
}
