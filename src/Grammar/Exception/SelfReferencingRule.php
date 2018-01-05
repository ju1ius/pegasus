<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Exception;

use ju1ius\Pegasus\Expression\Reference;

/**
 * @author ju1ius
 */
class SelfReferencingRule extends GrammarException
{
    /**
     * @param Reference $expr
     */
    public function __construct(Reference $expr)
    {
        $msg = sprintf(
            'A rule cannot be a reference to itself: `%s = %s`',
            $expr->getName(),
            $expr
        );
        parent::__construct($msg);
    }
}
