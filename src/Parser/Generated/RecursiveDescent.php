<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Parser\Generated;

use ju1ius\Pegasus\Exception\ParseError;
use ju1ius\Pegasus\Exception\IncompleteParseError;
use ju1ius\Pegasus\Node;

class RecursiveDescent implements ParserInterface
{
    /**
     * @var string
     */
    protected $text;

    /**
     * @var int
     */
    public $pos = 0;

    /**
     * @var ParseError
     */
    protected $error;

    /**
     * @var \Closure[]
     */
    protected $matchers = [];

    /**
     * RecursiveDescent constructor.
     */
    public function __construct()
    {
        $this->matchers = $this->buildMatchers();
    }

    /**
     * @inheritdoc
     */
    public function parseAll($text, $startRule = null)
    {
        $result = $this->parse($text, 0, $startRule);
        if ($this->pos < strlen($text)) {
            //echo $result->inspect(), "\n";
            throw new IncompleteParseError(
                $text,
                $this->pos,
                $this->error->expr
            );
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function parse($text, $position = 0, $startRule = null)
    {
        $this->text = $text;
        $this->pos = $position;
        $this->error = new ParseError($text);

        $result = $this->apply($startRule, $position);

        if (!$result) {
            throw $this->error;
        }

        return $result;
    }

    /**
     * Applies $rule_name at position $pos.
     *
     *
     * @param string $ruleName
     * @param int    $position
     *
     * @return Node|null
     */
    protected function apply($ruleName, $position = 0)
    {
        $this->pos = $position;
        $this->error->position = $position;
        $this->error->expr = $ruleName;

        // evaluate expression
        return $this->evaluate($ruleName);
    }

    /**
     * Evaluates an expression & updates current position on success.
     *
     * @param string $ruleName
     *
     * @return Node|null
     */
    final protected function evaluate($ruleName)
    {
        $result = $this->matchers[$ruleName]();
        if ($result) {
            $this->pos = $result->end;
            $this->error->node = $result;
        }

        return $result;
    }

    /**
     * @return \Closure[]
     */
    private function buildMatchers()
    {
        $matchers = [];
        $refClass = new \ReflectionClass($this);
        foreach ($refClass->getMethods() as $method) {
            if (strpos($method->name, 'match_') === 0) {
                $ruleName = substr($method->name, 6);
                $matchers[$ruleName] = $method->getClosure($this);
            }
        }

        return $matchers;
    }
}
