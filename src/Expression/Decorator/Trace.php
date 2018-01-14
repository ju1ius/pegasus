<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Expression\Decorator;


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

    public function match(string $text, Parser $parser)
    {
        $expr = $this->children[0];

        $parser->enterTrace($expr);
        $result = $expr->match($text, $parser);
        $parser->leaveTrace($expr, $result);

        return $result;
    }

    public function __toString(): string
    {
        return (string)$this->children[0];
    }
}
