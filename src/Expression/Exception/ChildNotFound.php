<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Expression\Exception;


class ChildNotFound extends \RuntimeException
{
    public function __construct(?int $offset = null)
    {
        $message = 'No child expression found';
        if ($offset !== null) {
            $message .= sprintf(' at offset %d', $offset);
        }

        parent::__construct($message);
    }
}
