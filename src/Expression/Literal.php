<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Parser\Scope;
use ju1ius\Pegasus\Utils\StringUtil;

/**
 * A string literal
 *
 * Use these if you can; they're the fastest.
 */
class Literal extends Terminal
{
    /**
     * @var string
     */
    public $literal;

    /**
     * @var string
     */
    public $quoteCharacter = '"';

    /**
     * @var int
     */
    public $length = 0;

    public function __construct($literal, $name = '', $quoteCharacter = '"')
    {
        parent::__construct($name);
        $this->literal = $literal;
        $this->quoteCharacter = $quoteCharacter;

        $this->length = strlen($this->literal);
    }

    public function __toString()
    {
        return sprintf(
            '%1$s%2$s%1$s',
            $this->quoteCharacter,
            addcslashes($this->literal, $this->quoteCharacter)
        );
    }

    public function match($text, $pos, ParserInterface $parser, Scope $scope)
    {
        if (substr($text, $pos, $this->length) === $this->literal) {
            return new Node\Terminal($this->name, $pos, $pos + $this->length, $this->literal);
        }
    }
}
