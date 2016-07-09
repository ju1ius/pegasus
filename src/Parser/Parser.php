<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Parser;

use ju1ius\Pegasus\Exception\IncompleteParseError;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Exception\ParseError;

abstract class Parser
{
    /**
     * @var Grammar
     */
    protected $grammar;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var int
     */
    public $pos = 0;

    /**
     * @var ParseError
     */
    protected $error;

    public function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;
    }

    /**
     * Return the parse tree matching this expression at the given position.
     *
     * @param string      $source
     * @param string|null $startRule
     *
     * @return Node|null
     *
     * @throws IncompleteParseError
     * @throws ParseError if there's no match there
     */
    final public function parseAll($source, $startRule = null)
    {
        $result = $this->parse($source, 0, $startRule);
        if ($this->pos < strlen($source)) {
            throw new IncompleteParseError(
                $source,
                $this->pos,
                $this->error
            );
        }

        return $result;
    }

    /**
     * Parse $text starting from position $pos, using start rule $startRule,
     * not necessarily extending all the way to the end of $text.
     *
     * @param string $text
     * @param int    $pos
     * @param string $startRule
     *
     * @return Node|null
     *
     * @throws ParseError if there's no match there
     */
    abstract public function parse($text, $pos = 0, $startRule = null);

    /**
     * Applies Expression $expr at position $pos.
     *
     * This is called internally by Expression::match to parse rule references.
     *
     * @internal
     *
     * @param string $rule
     * @param Scope  $scope
     *
     * @return Node|null
     */
    abstract public function apply($rule, Scope $scope);
}
