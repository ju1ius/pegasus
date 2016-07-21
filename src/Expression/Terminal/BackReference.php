<?php

namespace ju1ius\Pegasus\Expression\Terminal;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Terminal;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Node;
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

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function match($text, Parser $parser, Scope $scope)
    {
        $start = $parser->pos;
        $pattern = $scope[$this->identifier];
        $length = strlen($pattern);

        if ($pattern === substr($text, $start, $length)) {
            $end = $parser->pos += $length;

            return $parser->isCapturing ?
                new Node\Terminal($this->name, $start, $end, $pattern)
                : true;
        }

        $parser->registerFailure($this, $start);
    }

    public function __toString()
    {
        return sprintf('$%s', $this->identifier);
    }
}