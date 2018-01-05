<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Parser\Scope;

/**
 * Applies a rule inherited from a super-grammar.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
final class Super extends Expression
{
    /**
     * The name of the rule this expression refers to.
     *
     * @var string
     */
    private $identifier;

    public function __construct(string $identifier, string $name = '')
    {
        parent::__construct($name);
        $this->identifier = $identifier;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function isCapturingDecidable(): bool
    {
        return false;
    }

    public function match(string $text, Parser $parser)
    {
        $bindings = $parser->bindings;
        $parser->bindings = [];

        $result = $parser->apply($this->identifier, true);

        $parser->bindings = $bindings;

        return $result;
    }

    public function __toString(): string
    {
        return sprintf('super::%s', $this->identifier);
    }
}
