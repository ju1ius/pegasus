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
use ju1ius\Pegasus\Parser\ParserInterface;
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
     * @inheritDoc
     */
    public function __construct($pattern, array $flags = [], $name = '')
    {
        parent::__construct($name);
        $this->pattern = $pattern;
        $this->flags = array_unique(array_merge($flags, ['S', 'x']));

        $this->compiledPattern = $this->compilePattern();
    }

    /**
     * @inheritDoc
     */
    public function match($text, $pos, ParserInterface $parser, Scope $scope)
    {
        if (preg_match($this->compiledPattern, $text, $matches, 0, $pos)) {
            $match = $matches[0];
            $length = strlen($match);

            return new Node\Terminal($this->name, $pos, $pos + $length, $match);
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
