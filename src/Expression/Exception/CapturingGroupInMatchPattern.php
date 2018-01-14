<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Expression\Exception;


class CapturingGroupInMatchPattern extends \LogicException
{
    public function __construct(string $pattern, int $groupCount)
    {
        $message = sprintf(
            '%d capturing group%s found in pattern `%s`. Please use a `RegExp` object instead.',
            $groupCount,
            $groupCount > 1 ? 's' : '',
            $pattern
        );
        parent::__construct($message);
    }
}
