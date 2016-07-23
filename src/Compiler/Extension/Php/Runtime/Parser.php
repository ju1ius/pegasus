<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler\Extension\Php\Runtime;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Exception\IncompleteParseError;
use ju1ius\Pegasus\Parser\Exception\ParseError;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class Parser
{
    /**
     * @var string
     */
    protected $text;

    /**
     * @var int
     */
    protected $pos = 0;

    /**
     * @var bool
     */
    protected $isCapturing = true;

    /**
     * @var ParseError
     */
    protected $error;

    /**
     * @var \Closure[]
     */
    protected $matchers = [];

    /**
     * @var string
     */
    protected $startRule;

    /**
     * @inheritdoc
     */
    final public function parseAll($text, $startRule = null)
    {
        $result = $this->parse($text, 0, $startRule);
        if ($this->pos < strlen($text)) {
            throw new IncompleteParseError(
                $text,
                $this->pos,
                $this->error
            );
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function parse($text, $position = 0, $startRule = null)
    {
        if (!$this->matchers) {
            $this->matchers = $this->buildMatchers();
        }
        $this->text = $text;
        $this->pos = $position;
        $this->isCapturing = true;
        $this->error = new ParseError($text);

        // disable garbage collection while parsing for speed
        gc_disable();
        $result = $this->apply($startRule ?: $this->startRule);
        gc_enable();

        if (!$result) {
            throw $this->error;
        }

        return $result;
    }

    /**
     * Applies Expression $expr at position $pos.
     *
     * This is called internally by Expression::match to parse rule references.
     *
     * @internal
     *
     * @param string $rule  The rule name to apply
     *
     * @return Node|true|null
     */
    abstract protected function apply($rule);

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
