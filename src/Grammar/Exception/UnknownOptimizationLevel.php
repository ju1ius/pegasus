<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Exception;

class UnknownOptimizationLevel extends \LogicException
{
    public function __construct(int $level, array $availableLevels = [])
    {
        $msg = sprintf(
            'Unknown optimization level: `%s`. Available levels: %s',
            $level,
            implode(', ', array_map(function ($level) {
                return sprintf('`%s`', $level);
            }, $availableLevels))
        );
        parent::__construct($msg);
    }
}
