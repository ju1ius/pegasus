<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression\Terminal;

use ju1ius\Pegasus\Expression\Terminal;
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Parser;
use ju1ius\Pegasus\Parser\Scope;

/**
 * An expression that matches what a regex does.
 *
 * Use these as much as you can and jam as much into each one as you can: they're fast.
 */
final class RegExp extends Terminal
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @var array
     */
    private $flags;

    /**
     * @var string
     */
    private $compiledPattern;

    public function __construct($pattern, array $flags = [], $name = '')
    {
        parent::__construct($name);
        $this->pattern = $pattern;
        $this->flags = array_unique(array_filter($flags));

        $this->compiledPattern = $this->compilePattern();
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return array
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @return string
     */
    public function getCompiledPattern()
    {
        return $this->compiledPattern;
    }

    public function __toString()
    {
        return sprintf('/%s/%s', $this->pattern, implode('', $this->flags));
    }

    public function match($text, Parser $parser, Scope $scope)
    {
        $start = $parser->pos;
        if (preg_match($this->compiledPattern, $text, $matches, 0, $start)) {
            $match = $matches[0];
            $end = $parser->pos += strlen($match);
            if (!$parser->isCapturing) {
                return true;
            }

            return $parser->isCapturing
                ? new Node\Terminal($this->name, $start, $end, $match, ['matches' => $matches])
                : true;
        }

        $parser->registerFailure($this, $start);
    }

    /**
     * @return string
     */
    private function compilePattern()
    {
        return sprintf(
            '/\G%s/%s',
            $this->pattern,
            implode('', array_unique(array_merge($this->flags, ['S', 'x'])))
        );
    }
}
