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
     * @todo What if the binding is equal to the empty string ?
     */
    public function matches(string $text, Parser $parser): Terminal|bool
    {
        if (!isset($parser->bindings[$this->identifier])) {
            throw new UndefinedBinding($this->identifier, $parser->bindings);
        }

        $start = $parser->pos;
        $pattern = $parser->bindings[$this->identifier];
        $length = \strlen($pattern);

        if ($pattern === substr($text, $start, $length)) {
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
