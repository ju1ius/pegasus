<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Expression\Decorator;


use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Parser\Parser;


final class Trace extends Decorator
{
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