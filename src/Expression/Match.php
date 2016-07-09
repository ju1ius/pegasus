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
 * @author ju1ius <ju1ius@laposte.net>
 */
class Match extends Terminal
{
    /**
     * @var string
     */
    public $pattern;

    /**
     * @var string[]
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
     * @inheritDoc
     */
    public function __construct($pattern, array $flags = [], $name = '')
    {
        parent::__construct($name);
        $this->pattern = $pattern;
        $this->flags = $flags;

        $this->compiledPattern = $this->compilePattern();
        $this->compiledFlags = implode('', array_unique(array_merge($flags, ['S', 'x'])));
    }

    /**
     * @inheritDoc
     */
    public function match($text, Parser $parser, Scope $scope)
    {
        $start = $parser->pos;
        if (preg_match($this->compiledPattern, $text, $matches, 0, $start)) {
            $match = $matches[0];
            $end = $parser->pos += strlen($match);

            return $parser->isCapturing
                ? new Node\Terminal($this->name, $start, $end, $match)
                : true;
        }
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return sprintf('/%s/%s', $this->pattern, implode('', $this->flags));
    }

    /**
     * @return string
     */
    protected function compilePattern()
    {
        return sprintf(
            '/\G%s/%s',
            $this->pattern,
            implode('', $this->flags)
        );
    }


}
