<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression\Match;

use ju1ius\Pegasus\Expression\Match;

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
    public function __construct($word, $name = '')
    {
        $this->word = $word;
        $pattern = sprintf('\b%s\b', preg_quote($word, '/'));
        parent::__construct($pattern, [], $name);
    }

    /**
     * @return string
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return sprintf('`%s`', $this->word);
    }
}
