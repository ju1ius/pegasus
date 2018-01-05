<?php declare(strict_types=1);

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
            'Top-level expressions must have a name. None given for: `%s`',
            $expr
        );
        parent::__construct($msg);
    }
}
