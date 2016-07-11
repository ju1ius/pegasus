<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Parser\Scope;

/**
 * This class is only generated by optimizations.
 * Do not use it directly.
 *
 * @internal
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
final class GroupMatch extends Terminal
{
    /**
     * @var Match
     */
    private $matcher;

    /**
     * @var int
     */
    private $groupCount;

    /**
     * GroupMatch constructor.
     *
     * @param Match  $match
     * @param int    $groupCount
     * @param string $name
     */
    public function __construct(Match $match, $groupCount, $name = '')
    {
        $this->matcher = $match;
        $this->groupCount = $groupCount;

        parent::__construct($name);
    }

    /**
     * @return Match
     */
    public function getMatcher()
    {
        return $this->matcher;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->matcher->pattern;
    }

    /**
     * @return string[]
     */
    public function getFlags()
    {
        return $this->matcher->flags;
    }

    /**
     * @return int
     */
    public function getCaptureCount()
    {
        return $this->groupCount;
    }

    /**
     * @inheritDoc
     */
    public function match($text, Parser $parser, Scope $scope)
    {
        $start = $parser->pos;
        if (preg_match($this->matcher->compiledPattern, $text, $matches, PREG_OFFSET_CAPTURE, $start)) {
            $end = $parser->pos += strlen($matches[0]);
            if (!$parser->isCapturing) {
                return true;
            }

            if ($this->groupCount === 1) {
                list($offset, $match) = $matches[1];

                return new Node\Terminal($this->name, $offset, $offset + strlen($match), $match);
            }

            $children = [];
            foreach (array_slice($matches, 1) as list($offset, $match)) {
                $children[] = new Node\Terminal($this->name, $offset, $offset + strlen($match), $match);
            }

            return new Node\Composite($this->name, $start, $end, $children);
        }
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return sprintf('GroupMatch[%s, %d]', $this->matcher->compiledPattern, $this->groupCount);
    }
}