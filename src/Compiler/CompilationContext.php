<?php declare(strict_types=1);
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
final class CompilationContext
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
    private function __construct(Grammar $grammar, int $type = self::TYPE_CAPTURING)
    {
        $this->grammar = $grammar;
        $this->type = $type;
        $this->analysis = new Analysis($grammar);
    }

    public static function of(Grammar $grammar): self
    {
        return new self($grammar);
    }

    public function ofRule(string $ruleName): self
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
    public function matching(): self
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
    public function capturing(): self
    {
        if ($this->isCapturing()) {
            return $this;
        }

        $ctx = clone $this;
        $ctx->type = self::TYPE_CAPTURING;

        return $ctx;
    }

    public function getGrammar(): Grammar
    {
        return $this->grammar;
    }

    public function getAnalysis(): Analysis
    {
        return $this->analysis;
    }

    public function getRule(): string
    {
        return $this->ruleName;
    }

    public function isCapturing(): bool
    {
        return $this->type === self::TYPE_CAPTURING;
    }

    public function isMatching(): bool
    {
        return $this->type === self::TYPE_MATCHING;
    }

    public function needsBindings(string $ruleName): bool
    {
        return $this->analysis->canModifyBindings($ruleName);
    }
}
