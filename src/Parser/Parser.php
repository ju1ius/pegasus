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

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\Exception\IncompleteParseError;
use ju1ius\Pegasus\Parser\Exception\ParseError;

abstract class Parser
{
    /**
     * The grammar used to parse the input string.
     *
     * @var Grammar
     */
    protected $grammar;

    /**
     * The input string.
     *
     * @var string
     */
    protected $source;

    /**
     * The current position into the input string.
     *
     * @var int
     */
    public $pos = 0;

    /**
     * Flag that can be set by expressions to signal whether their children
     * should return parse nodes or just true on success.
     *
     * @var bool
     */
    public $isCapturing = true;

    /**
     * @var bool
     */
    public $isTracing = false;

    /**
     * @var ParseError
     */
    public $error;

    public function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;
    }

    /**
     * Parse the entire text, using given start rule or the grammar's one,
     * requiring the entire input to match the grammar.
     *
     * @param string      $source
     * @param string|null $startRule
     *
     * @return Node|true|null
     *
     * @throws IncompleteParseError If parsing was successful but did not consume all the input.
     * @throws ParseError If the input doesn't match the grammar.
     */
    final public function parseAll($source, $startRule = null)
    {
        $result = $this->parse($source, 0, $startRule);
        if ($this->pos < strlen($source)) {
            throw new IncompleteParseError(
                $source,
                $this->pos
            );
        }

        return $result;
    }

    /**
     * Parse text starting from given position, using given start rule or the grammar's one,
     * but does not require the entire input to match the grammar.
     *
     * @param string $text
     * @param int    $pos
     * @param string $startRule
     *
     * @return Node|true|null
     *
     * @throws ParseError If the input doesn't match the grammar.
     */
    abstract public function parse($text, $pos = 0, $startRule = null);

    /**
     * Applies a grammar rule.
     *
     * This is called internally by Reference::match.
     *
     * @internal
     *
     * @param string $rule  The rule name to apply
     * @param Scope  $scope The current scope
     * @param bool   $super Whether we should explicitely apply a parent rule
     *
     * @return Node|null
     */
    abstract public function apply($rule, Scope $scope, $super = false);
}
