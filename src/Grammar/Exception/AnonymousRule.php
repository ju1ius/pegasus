<?php

namespace ju1ius\Pegasus\Grammar\Exception;

use ju1ius\Pegasus\Expression;

/**
 * @author ju1ius
 */
class AnonymousTopLevelExpression extends GrammarException
{
    public function __construct(Expression $expr)
    {
        $msg = sprintf(
            'Top-level expressions must have a name. None given for <%s>',
            $expr->asRightHandSide()
        );
        parent::__construct($msg);
    }
}
