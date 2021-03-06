<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Parser\Exception;



/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class UndefinedBinding extends \RuntimeException
{
    public function __construct(string $name, array $scope)
    {
        $message = sprintf(
            'Named binding `%s` was not found in the current scope `%s`',
            $name,
            var_export($scope, true)
        );
        parent::__construct($message);
    }
}
