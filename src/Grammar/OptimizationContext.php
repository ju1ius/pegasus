<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Exception\MissingStartRule;

/**
 * Provides contextual information to optimizations.
 */
final class OptimizationContext
{
    const TYPE_MATCHING = 1;
    const TYPE_CAPTURING = 2;

    private Analysis $analysis;

    /**
     * @param Grammar $grammar
     * @param int     $type
     */
    private function __construct(
        private Grammar $grammar,
        private int $type = self::TYPE_CAPTURING
    ) {
        $this->analysis = new Analysis($grammar);
    }

    public static function of(Grammar $grammar, int $type = self::TYPE_CAPTURING): self
    {
        return new self($grammar, $type);
    }

    /**
     * Returns a new matching context for the grammar.
     */
    public function matching(): self
    {
        return self::of($this->grammar, self::TYPE_MATCHING);
    }

    /**
     * Returns a new capturing context for the grammar.
     */
    public function capturing(): self
    {
        return self::of($this->grammar, self::TYPE_CAPTURING);
    }

    public function isCapturing(): bool
    {
        return $this->type === self::TYPE_CAPTURING;
    }

    public function isMatching(): bool
    {
        return $this->type === self::TYPE_MATCHING;
    }

    public function getAnalysis(): Analysis
    {
        return $this->analysis;
    }

    public function getRule(string $ruleName): Expression
    {
        return $this->grammar[$ruleName];
    }

    /**
     * @throws MissingStartRule
     */
    public function getStartRule(): string
    {
        return $this->grammar->getStartRule();
    }

    /**
     * @return string[]
     * @throws MissingStartRule
     */
    public function getReferencedRules(): array
    {
        $startRule = $this->getStartRule();

        return array_merge([$startRule], $this->analysis->getReferencesFrom($startRule));
    }

    public function isInlineableRule(string $ruleName): bool
    {
        return $this->grammar->isInlined($ruleName)
            && $this->analysis->isRegular($ruleName);
    }
}
