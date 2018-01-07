<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Source\Exception;


class PositionNotFound extends \RuntimeException
{
    public function __construct(int $line, int $column)
    {
        parent::__construct(sprintf(
            'No offset found at line %d, column %d',
            $line,
            $column
        ));
    }
}