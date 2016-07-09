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
use ju1ius\Pegasus\Parser\Parser;
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

    public function __construct($pattern, array $flags = [], $name = '')
    {
        parent::__construct($name);
        $this->pattern = $pattern;
        $this->flags = $flags;

        $this->compiledFlags = array_unique(array_merge($flags, ['S', 'x']));
        $this->compiledPattern = sprintf(
            '/\G%s/%s',
            $this->pattern,
            implode('', $this->compiledFlags)
        );
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

            return new Node\Terminal(
                $this->name,
                $start,
                $parser->pos += strlen($match),
                $match,
                ['matches' => $matches]
            );
        }
    }
}
