<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Terminal;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Terminal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Exception\UndefinedBinding;
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Parser\Scope;

/**
 * @author ju1ius
 */
final class BackReference extends Terminal
{
    /**
     * @var string
     */
    private $identifier;

    public function __construct(string $identifier, string $name = '')
    {
        $this->identifier = $identifier;
        parent::__construct($name);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @inheritdoc
     *
     * @todo What if the binding is equal to the empty string ?
     */
    public function match(string $text, Parser $parser)
    {
        if (!isset($parser->bindings[$this->identifier])) {
            throw new UndefinedBinding($this->identifier, $parser->bindings);
        }

        $start = $parser->pos;
        $pattern = $parser->bindings[$this->identifier];
        $length = strlen($pattern);

        if ($pattern === substr($text, $start, $length)) {
            $end = $parser->pos += $length;

            return $parser->isCapturing ?
                new Node\Terminal($this->name, $start, $end, $pattern)
                : true;
        }

        $parser->registerFailure($this, $start);
    }

    public function __toString(): string
    {
        return sprintf('$%s', $this->identifier);
    }
}
