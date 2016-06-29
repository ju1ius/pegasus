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
        $msg = "Rule '$ruleName' could not be found in this grammar.";
        parent::__construct($msg);
    }
}
