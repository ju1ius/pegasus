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
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Analysis;

/**
 * Provides contextual information to optimizations.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
final class OptimizationContext
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
     * @return OptimizationContext
     */
    public static function of(Grammar $grammar, $type = self::TYPE_CAPTURING)
    {
        return new self($grammar, $type);
    }

    /**
     * Returns a new matching context for the grammar.
     *
     * @return OptimizationContext
     */
    public function matching()
    {
        return self::of($this->grammar, self::TYPE_MATCHING);
    }

    /**
     * Returns a new capturing context for the grammar.
     *
     * @return OptimizationContext
     */
    public function capturing()
    {
        return self::of($this->grammar, self::TYPE_CAPTURING);
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
     * @return Analysis
     */
    public function getAnalysis()
    {
        return $this->analysis;
    }

    /**
     * @param string $ruleName
     *
     * @return Expression
     * @throws Grammar\Exception\RuleNotFound If rule was not found in the grammar
     */
    public function getRule($ruleName)
    {
        return $this->grammar[$ruleName];
    }

    /**
     * @return string
     * @throws \ju1ius\Pegasus\Grammar\Exception\MissingStartRule
     */
    public function getStartRule()
    {
        return $this->grammar->getStartRule();
    }

    public function getReferencedRules()
    {
        $startRule = $this->getStartRule();

        return array_merge([$startRule], $this->analysis->getReferencesFrom($startRule));
    }

    /**
     * @param string $ruleName
     *
     * @return bool
     */
    public function isInlineableRule($ruleName)
    {
        return $this->grammar->isInlined($ruleName)
            && $this->analysis->isRegular($ruleName);
    }
}
