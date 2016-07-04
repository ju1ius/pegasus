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

use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Parser\Scope;
use ju1ius\Pegasus\Utils\StringUtil;

/**
 * An expression that matches what a regex does.
 *
 * Use these as much as you can and jam as much into each one as you can: they're fast.
 */
class RegExp extends Terminal
{
    /**
     * @var string
     */
    public $pattern;

    /**
     * @var array
     */
    public $flags;

    /**
     * @var string
     */
    public $compiledPattern;

    /**
     * @var string
     */
    public $compiledFlags;

    /**
     * @var boolean
     */
    public $hasBackReference = false;

    /**
     * @var array
     */
    public $subjectParts = [];

    public function __construct($pattern, $name = '', array $flags = [])
    {
        parent::__construct($name);
        $this->pattern = $pattern;
        $this->flags = array_unique(array_merge($flags, ['S', 'x']));

        $this->compiledFlags = implode('', $this->flags);
        $this->compiledPattern = sprintf(
            '/\G%s/%s',
            $this->pattern,
            $this->compiledFlags
        );
        // check for backreferences
        $parts = StringUtil::splitBackReferenceSubject($this->pattern);
        if ($parts) {
            $this->hasBackReference = true;
            $this->subjectParts = $parts;
        }
    }

    public function __toString()
    {
        return $this->compiledPattern;
    }

    public function match($text, $pos, ParserInterface $parser, Scope $scope)
    {
        if ($this->hasBackReference) {
            $backRef = StringUtil::replaceBackReferenceSubject($this->subjectParts, function ($label) use ($scope) {
                return $scope[$label];
            }, true);
            $pattern = '/\G' . $backRef . '/' . $this->compiledFlags;
        } else {
            $pattern = $this->compiledPattern;
        }
        if (preg_match($pattern, $text, $matches, 0, $pos)) {
            $match = $matches[0];
            $length = strlen($match);
            $node = new Node\RegExp($this->name, $text, $pos, $pos + $length, $matches);

            return $node;
        }
    }
}
