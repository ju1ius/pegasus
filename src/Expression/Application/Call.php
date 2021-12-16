<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Application;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Node\ExternalReference;
use ju1ius\Pegasus\Expression\Application;
use ju1ius\Pegasus\Grammar\Exception\TraitNotFound;
use ju1ius\Pegasus\Parser\Parser;

/**
 * Calls a rule from an imported grammar.
 */
class Call extends Application
{
    public function __construct(
        private string $namespace,
        private string $identifier,
        string $name = ''
    ) {
        parent::__construct($name);
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @throws TraitNotFound
     */
    public function matches(string $text, Parser $parser): Node|bool
    {
        // backup state
        $start = $parser->pos;
        $grammar = $parser->grammar;
        $capturing = $parser->isCapturing;
        // update state
        $trait = $grammar->getTrait($this->namespace);
        $parser->grammar = $trait;
        $parser->pushScope();

        $result = $parser->apply($trait[$this->identifier]);

        // restore state
        $parser->popScope();
        $parser->grammar = $grammar;

        if ($capturing && $result && $result !== true) {
            return new ExternalReference(
                $this->namespace,
                $this->name,
                $start,
                $parser->pos,
                $result
            );
        }

        return $result;
    }

    public function __toString(): string
    {
        return sprintf('%s::%s', $this->namespace, $this->identifier);
    }
}
