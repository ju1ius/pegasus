<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar\Exception;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class UnknownOptimizationLevel extends \LogicException
{
    /**
     * @inheritDoc
     */
    public function __construct($level, array $availableLevels = [])
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
