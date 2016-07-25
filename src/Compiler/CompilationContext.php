<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Analysis;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class CompilationContext
{
    const TYPE_MATCHING = 1;
    const TYPE_CAPTURING = 2;

    /**
     * @var Grammar
     */
    private $grammar;

    /**
     * @var int
     */
    private $type;

    /**
     * @var Analysis
     */
    private $analysis;

    /**
     * @var string
     */
    private $ruleName;

    /**
     * @param Grammar $grammar
     * @param int     $type
     */
    private function __construct(Grammar $grammar, $type = self::TYPE_CAPTURING)
    {
        $this->grammar = $grammar;
        $this->type = $type;
        $this->analysis = new Analysis($grammar);
    }

    /**
     * @param Grammar $grammar
     * @param int     $type
     *
     * @return CompilationContext
     */
    public static function of(Grammar $grammar, $type = self::TYPE_CAPTURING)
    {
        return new self($grammar, $type);
    }

    /**
     * @param string $ruleName
     *
     * @return CompilationContext
     */
    public function ofRule($ruleName)
    {
        $ctx = clone $this;
        $ctx->type = self::TYPE_CAPTURING;
        $ctx->ruleName = $ruleName;

        return $ctx;
    }

    /**
     * Returns a new matching context for the grammar.
     *
     * @return CompilationContext
     */
    public function matching()
    {
        if ($this->isMatching()) {
            return $this;
        }

        $ctx = clone $this;
        $ctx->type = self::TYPE_MATCHING;

        return $ctx;
    }

    /**
     * Returns a new capturing context for the grammar.
     *
     * @return CompilationContext
     */
    public function capturing()
    {
        if ($this->isCapturing()) {
            return $this;
        }

        $ctx = clone $this;
        $ctx->type = self::TYPE_CAPTURING;

        return $ctx;
    }

    /**
     * @return Grammar
     */
    public function getGrammar()
    {
        return $this->grammar;
    }

    /**
     * @return Analysis
     */
    public function getAnalysis()
    {
        return $this->analysis;
    }

    /**
     * @return string
     */
    public function getRule()
    {
        return $this->ruleName;
    }

    /**
     * @return bool
     */
    public function isCapturing()
    {
        return $this->type === self::TYPE_CAPTURING;
    }

    /**
     * @return bool
     */
    public function isMatching()
    {
        return $this->type === self::TYPE_MATCHING;
    }

    /**
     * @param string $ruleName
     *
     * @return bool
     */
    public function needsBindings($ruleName)
    {
        return $this->analysis->canModifyBindings($ruleName);
    }
}
