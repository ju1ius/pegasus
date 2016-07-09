<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Parser\Scope;

/**
 * @author ju1ius
 */
class BackReference extends Terminal
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * BackReference constructor.
     *
     * @param string $identifier
     * @param string $name
     */
    public function __construct($identifier, $name = '')
    {
        $this->identifier = $identifier;
        parent::__construct($name);
    }

    public function match($text, $pos, ParserInterface $parser, Scope $scope)
    {
        $pattern = $scope[$this->identifier];
        $length = strlen($pattern);

        if ($pattern === $value = substr($text, $pos, $length)) {
            return new Node\Terminal($this->name, $pos, $pos + $length, $value);
        }
    }

    public function __toString()
    {
        return sprintf('$%s', $this->identifier);
    }
}
