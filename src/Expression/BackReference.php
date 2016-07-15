<?php

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Parser\Scope;

/**
 * @author ju1ius
 */
class BackReference extends Terminal
{
    /**
     * @var string
     */
    public $identifier;

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

        if ($start > $parser->error->position) {
            $parser->error->position = $start;
            $parser->error->expr = $this;
        }
    }

    public function __toString()
    {
        return sprintf('$%s', $this->identifier);
    }
}
