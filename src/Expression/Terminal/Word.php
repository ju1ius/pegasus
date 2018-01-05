<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression\Terminal;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class Word extends Match
{
    /**
     * @var string
     */
    private $word;

    /**
     * @inheritDoc
     */
    public function __construct(string $word, string $name = '')
    {
        $this->word = $word;
        $pattern = sprintf('\b%s\b', preg_quote($word, '/'));
        parent::__construct($pattern, [], $name);
    }

    public function getWord(): string
    {
        return $this->word;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return sprintf('`%s`', $this->word);
    }
}
