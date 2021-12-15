<?php declare(strict_types=1);

namespace ju1ius\Pegasus\RegExp\Exception;

/**
 * A regexp pattern contains unbalanced parentheses.
 */
final class MissingClosingParenthesis extends \LogicException
{
    public function __construct(string $pattern)
    {
        parent::__construct(sprintf(
            'The following pattern is missing a closing parenthesis: `%s`',
            $pattern
        ));
    }
}
