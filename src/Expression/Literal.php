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

    /**
     * @var boolean
     */
    public $hasBackReference = false;

    /**
     * @var array
     */
    public $subjectParts = [];

    public function __construct($literal, $name = '', $quoteCharacter = '"')
    {
        parent::__construct($name);
        $this->literal = $literal;
        $this->quoteCharacter = $quoteCharacter;

        $parts = StringUtil::splitBackReferenceSubject($this->literal);
        if ($parts) {
            $this->hasBackReference = true;
            $this->subjectParts = $parts;
        } else {
            $this->length = strlen($this->literal);
        }
    }

    public function asRightHandSide()
    {
        //TODO backslash escaping
        return sprintf('"%s"', $this->literal);
    }

    public function match($text, $pos, ParserInterface $parser, Scope $scope)
    {
        $value = $this->literal;
        $length = $this->length;

        if ($this->hasBackReference) {
            $value = StringUtil::replaceBackReferenceSubject($this->subjectParts, function ($identifier) use ($scope) {
                return $scope[$identifier];
            });
            $length = strlen($value);
        }
        if ($pos === strpos($text, $value, $pos)) {
            return new Node\Literal($this, $text, $pos, $pos + $length);
        }
    }
}
