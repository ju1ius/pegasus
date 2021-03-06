<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression\Application;

use ju1ius\Pegasus\Expression\Application;
use ju1ius\Pegasus\Parser\Parser;

/**
 * A reference to a grammar rule.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
final class Reference extends Application
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

    public function __toString(): string
    {
        return $this->identifier;
    }

    public function match(string $text, Parser $parser)
    {
        $bindings = $parser->bindings;
        $parser->bindings = [];

        $expr = $parser->grammar[$this->identifier];
        $result = $parser->apply($expr);

        $parser->bindings = $bindings;

        return $result;
    }
}
