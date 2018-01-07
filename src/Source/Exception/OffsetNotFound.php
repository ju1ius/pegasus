<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Source\Exception;


class OffsetNotFound extends \RangeException
{
    public function __construct(int $offset)
    {
        parent::__construct(sprintf(
            'Source offset %d not found.',
            $offset
        ));
    }
}