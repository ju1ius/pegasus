<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Exception;

/**
 * @author ju1ius
 */
class MissingStartRule extends GrammarException
{
    public function __construct()
    {
        parent::__construct('You must provide a start rule for the grammar.');
    }
}
