<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Expression\Application;


use ju1ius\Pegasus\CST\Node\ExternalReference;
use ju1ius\Pegasus\Expression\Application;
use ju1ius\Pegasus\Parser\Parser;


/**
 * Calls a rule from an imported grammar.
 */
class Call extends Application
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $identifier;

    public function __construct(string $namespace, string $identifier, string $name = '')
    {
        parent::__construct($name);
        $this->namespace = $namespace;
        $this->identifier = $identifier;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function match(string $text, Parser $parser)
    {
        // backup state
        $start = $parser->pos;
        $grammar = $parser->grammar;
        $bindings = $parser->bindings;
        $capturing = $parser->isCapturing;
        // update state
        $trait = $grammar->getTrait($this->namespace);
        $parser->grammar = $trait;
        $parser->bindings = [];

        $result = $parser->apply($trait[$this->identifier]);
        if ($capturing && $result && $result !== true) {
            $result = new ExternalReference(
                $this->namespace,
                $this->name,
                $start,
                $parser->pos,
                $result
            );
        }

        // restore state
        $parser->grammar = $grammar;
        $parser->bindings = $bindings;

        return $result;
    }

    public function __toString(): string
    {
        return sprintf('%s::%s', $this->namespace, $this->identifier);
    }
}
