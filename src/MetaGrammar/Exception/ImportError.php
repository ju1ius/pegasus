<?php declare(strict_types=1);


namespace ju1ius\Pegasus\MetaGrammar\Exception;


class ImportError extends \RuntimeException
{
    public function __construct(string $message = "", \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
