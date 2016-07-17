<?php

namespace ju1ius\Pegasus\Grammar\Exception;

use ju1ius\Pegasus\Expression\Reference;

/**
 * @author ju1ius
 */
class CircularReference extends GrammarException
{
    /**
     * @param Reference $expr
     * @param string[]  $referenceChain
     */
    public function __construct(Reference $expr, array $referenceChain = [])
    {
        $msg = sprintf('Circular reference in rule <%s>.', $expr->getIdentifier());
        if ($referenceChain) {
            $msg .= sprintf(' Reference chain: %s', implode(' => ', $referenceChain));
        }
        parent::__construct($msg);
    }
}
