<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Parser\Exception;

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
