<?php declare(strict_types=1);

namespace ju1ius\Pegasus\RegExp\Exception;

class UnmatchedClosingParenthesis extends \LogicException
{

    /**
     * @param string $pattern
     * @param int    $offset
     */
    public function __construct(string $pattern, int $offset)
    {
        parent::__construct(sprintf(
            'Unmatched closing parenthesis at offset %d in pattern `%s`',
            $offset,
            $pattern
        ));
    }
}
