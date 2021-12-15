<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Parser\Parser;

final class Trace extends Decorator
{
    public function __construct(Expression $child = null, string $name = '')
    {
        parent::__construct($child, $name);
        $this->id = $child->id;
        $this->name = $child->name;
    }

    public function matches(string $text, Parser $parser): Node|bool
    {
        $expr = $this->children[0];

        $parser->enterTrace($expr);
        $result = $expr->matches($text, $parser);
        $parser->leaveTrace($expr, $result);

        return $result;
    }

    public function __toString(): string
    {
        return (string)$this->children[0];
    }
}
