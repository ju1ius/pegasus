<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Exception;

use ju1ius\Pegasus\Expression\Terminal\CapturingRegExp;

class CapturingGroupInNonCapturingPattern extends \LogicException
{
    public function __construct(string $pattern, int $groupCount)
    {
        $message = sprintf(
            '%d capturing group%s found in pattern `%s`. Please use a `%s` object instead.',
            $groupCount,
            $groupCount > 1 ? 's' : '',
            $pattern,
            CapturingRegExp::class,
        );
        parent::__construct($message);
    }
}
