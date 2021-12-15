<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Application;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression\Application;
use ju1ius\Pegasus\Grammar\Exception\RuleNotFound;
use ju1ius\Pegasus\Parser\Parser;

/**
 * Applies a rule inherited from a super-grammar.
 */
final class Super extends Application
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

    /**
     * @throws RuleNotFound
     */
    public function matches(string $text, Parser $parser): Node|bool
    {
        $bindings = $parser->bindings;
        $parser->bindings = [];

        $expr = $parser->grammar->super($this->identifier);
        $result = $parser->apply($expr);

        $parser->bindings = $bindings;

        return $result;
    }

    public function __toString(): string
    {
        return sprintf('super::%s', $this->identifier);
    }
}
