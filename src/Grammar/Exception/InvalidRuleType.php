<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Grammar\Exception;


use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Utils\Str;


class InvalidRuleType extends GrammarException
{
    public function __construct($value)
    {
        $message = sprintf(
            'Expected an instance of `%s`,  but got: `%s`',
            Expression::class,
            Str::getType($value)
        );

        parent::__construct($message);
    }
}
