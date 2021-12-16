<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Parser\Exception;

use ju1ius\Pegasus\Parser\Scope;

class UndefinedBinding extends \RuntimeException
{
    public function __construct(string $name, Scope $scope)
    {
        $message = sprintf(
            'Named binding `%s` was not found in the current scope `%s`',
            $name,
            var_export($scope->bindings, true)
        );
        parent::__construct($message);
    }
}
