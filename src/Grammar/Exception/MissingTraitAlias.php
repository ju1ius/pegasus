<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Grammar\Exception;


class MissingTraitAlias extends GrammarException
{
    public function __construct()
    {
        $message = 'Cannot add anonymous grammar without an alias.';
        parent::__construct($message);
    }
}
