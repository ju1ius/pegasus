<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Terminal;

use ju1ius\Pegasus\Expression\TerminalExpression;
use ju1ius\Pegasus\RegExp\Normalizer;

/**
 * Base class for the Match and RegExp terminal expressions.
 */
abstract class AbstractRegExp extends TerminalExpression
{
    protected string $pattern;
    /**
     * @var string[]
     */
    protected array $flags;

    protected string $compiledPattern;
    protected string $compiledFlags;

    public function __construct(string $pattern, array $flags = [], string $name = '')
    {
        parent::__construct($name);
        $this->flags = array_unique(array_filter($flags));
        $this->pattern = Normalizer::normalize($pattern, $this->compileFlags());
        $this->compiledPattern = $this->compilePattern();
        $this->compiledFlags = implode('', $this->compileFlags());
    }

    final public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @return string[]
     */
    final public function getFlags(): array
    {
        return $this->flags;
    }

    final public function getCompiledPattern(): string
    {
        return $this->compiledPattern;
    }

    final public function getCompiledFlags(): string
    {
        return $this->compiledFlags;
    }

    public function __toString(): string
    {
        return sprintf('/%s/%s', $this->pattern, implode('', $this->flags));
    }

    private function compilePattern(): string
    {
        return "\G{$this->pattern}";
    }

    /**
     * @return string[]
     */
    private function compileFlags(): array
    {
        return array_unique(array_merge($this->flags, ['x']));
    }
}
