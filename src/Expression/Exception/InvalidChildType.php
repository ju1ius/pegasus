<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Expression\Exception;


use ju1ius\Pegasus\Expression;


class InvalidChildType extends \LogicException
{
    public function __construct($child, string $expectedClass = Expression::class)
    {
        $message = sprintf(
            'Expected an instance of `%s`,  but got: `%s`',
            $expectedClass,
            get_debug_type($child)
        );

        parent::__construct($message);
    }
}
