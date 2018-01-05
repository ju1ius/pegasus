<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Exception;

/**
 * @author ju1ius
 */
final class UnresolvedReference extends GrammarException
{
    public function __construct(string $identifier)
    {
        $msg = "Reference to rule <{$identifier}> could not be resolved.";
        $msg .= ' Check the identifier for typos and make sure you call Grammar::finalize() before matching.';
        parent::__construct($msg);
    }
}
