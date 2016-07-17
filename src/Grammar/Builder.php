<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Grammar;

/**
 * This class provides a fluent interface for building grammars.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class Builder extends ExpressionBuilder
{
    /**
     * Current rule name.
     *
     * @var string
     */
    protected $currentRule;

    /**
     * @var Grammar
     */
    protected $grammar;

    /**
     * Builder constructor.
     *
     * @param Grammar $grammar
     */
    protected function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;
        parent::__construct();
    }

    /**
     * @param string $name
     *
     * @return Builder
     */
    public static function create($name = '')
    {
        $grammar = new Grammar();
        if ($name) {
            $grammar->setName($name);
        }

        return new self($grammar);
    }

    /**
     * @param Grammar $grammar
     *
     * @return Builder
     */
    public static function of(Grammar $grammar)
    {
        return new self($grammar);
    }

    /**
     * Ends the current rule and adds it to the grammar.
     *
     * @return $this
     */
    public function endRule()
    {
        $this->endAll();
        if ($this->rootExpr && $this->compositeStack->isEmpty()) {
            $this->grammar[$this->currentRule] = $this->rootExpr;
            $this->rootExpr = null;
        }

        return $this;
    }

    /**
     * @return Grammar
     */
    public function getGrammar()
    {
        $this->endRule();

        return $this->grammar;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function rule($name)
    {
        $this->endRule();
        $this->currentRule = $name;

        return $this;
    }

    /**
     * @param string $identifier Defaults to the current rule.
     *
     * @return $this
     */
    public function super($identifier = '')
    {
        $identifier = $identifier ?: $this->currentRule;

        return parent::super($identifier);
    }
}
