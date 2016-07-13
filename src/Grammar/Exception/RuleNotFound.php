<?php

namespace ju1ius\Pegasus\Grammar\Exception;

/**
 * @author ju1ius
 */
class RuleNotFound extends GrammarException
{
    /**
     * @param string $ruleName The name of the unknown rule.
     */
    public function __construct($ruleName)
    {
        parent::__construct(sprintf(
            'Rule `%s` could not be found in this grammar.',
            $ruleName
        ));
    }
}
