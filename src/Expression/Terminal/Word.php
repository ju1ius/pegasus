<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Terminal;

final class Word extends CapturingRegExp
{
    public function __construct(
        private string $word,
        string $name = ''
    ) {
        $pattern = sprintf('\b%s\b', preg_quote($word, '/'));
        parent::__construct($pattern, [], $name);
    }

    public function getWord(): string
    {
        return $this->word;
    }

    public function __toString(): string
    {
        return sprintf('`%s`', $this->word);
    }
}
