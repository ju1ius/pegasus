<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Terminal;

use ju1ius\Pegasus\Expression\TerminalExpression;
use ju1ius\Pegasus\RegExp\Normalizer;

/**
 * Base class for the Match and RegExp terminal expressions.
 */
abstract class RegExp extends TerminalExpression
{
    /**
     * @var string
     */
    protected string $pattern;

    /**
     * @var string[]
     */
    protected array $flags;

    /**
     * @var string
     */
    protected string $compiledPattern;

    public function __construct(string $pattern, array $flags = [], string $name = '')
    {
        parent::__construct($name);
        $this->flags = array_unique(array_filter($flags));
        $this->pattern = Normalizer::normalize($pattern, $this->compileFlags());
        $this->compiledPattern = $this->compilePattern();
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

    public function __toString(): string
    {
        return sprintf('/%s/%s', $this->pattern, implode('', $this->flags));
    }

    private function compilePattern(): string
    {
        return sprintf(
            '/\G%s/%s',
            $this->pattern,
            implode('', $this->compileFlags())
        );
    }

    /**
     * @return string[]
     */
    private function compileFlags(): array
    {
        return array_unique(array_merge($this->flags, ['S', 'x']));
    }
}
