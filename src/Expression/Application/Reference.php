<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Application;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression\Application;
use ju1ius\Pegasus\Parser\Parser;

/**
 * A reference to a grammar rule.
 */
final class Reference extends Application
{
    public function __construct(
        /**
         * The name of the rule this expression refers to.
         */
        private string $identifier,
        string $name = ''
    ) {
        parent::__construct($name);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function __toString(): string
    {
        return $this->identifier;
    }

    public function matches(string $text, Parser $parser): Node|bool
    {
        $expr = $parser->grammar[$this->identifier];

        $scope = $parser->scope;
        $parser->scope = clone $scope;

        $result = $parser->apply($expr);

        $parser->scope = $scope;
        return $result;
    }
}
