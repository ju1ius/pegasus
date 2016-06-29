<?php

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
            'A rule cannot be a reference to itself. Found: (%s)',
            $expr->asRule()
        );
        parent::__construct($msg);
    }
}