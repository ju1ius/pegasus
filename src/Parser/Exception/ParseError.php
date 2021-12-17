<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Parser\Exception;

use ju1ius\Pegasus\Trace\Trace;

class ParseError extends \RuntimeException
{
    private ?Trace $parsingTrace = null;

    public function __construct(string $message = '', ?Trace $trace = null)
    {
        parent::__construct($message);
        $this->parsingTrace = $trace;
    }

    public function getParsingTrace(): ?Trace
    {
        return $this->parsingTrace;
    }
}
