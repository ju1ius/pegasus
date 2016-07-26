<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Parser\Exception;

use ju1ius\Pegasus\Parser\Scope;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class UndefinedBinding extends \RuntimeException
{
    public function __construct($name, array $scope)
    {
        $message = sprintf(
            'Named binding `%s` was not found in the current scope `%s`',
            $name,
            var_export($scope, true)
        );
        parent::__construct($message);
    }
}
