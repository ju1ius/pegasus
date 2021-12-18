<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Parser;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;

class RecursiveDescentParser extends Parser
{
    public function apply(Expression $expr): Node|bool
    {
        return $expr->matches($this->source, $this);
    }
}
