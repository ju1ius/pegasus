<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Grammar\Exception;


class TraitNotFound extends GrammarException
{
    public function __construct(string $alias)
    {
        $message = sprintf(
            'No trait found with alias "%s". Did you forget to call Grammar::use() ?',
            $alias
        );

        parent::__construct($message);
    }
}
