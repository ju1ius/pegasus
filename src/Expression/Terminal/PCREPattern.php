<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Expression\Terminal;


use ju1ius\Pegasus\Expression\Terminal;
use ju1ius\Pegasus\RegExp\Formatter;


/**
 * Base class for the Match and RegExp terminal expressions.
 */
abstract class PCREPattern extends Terminal
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var string[]
     */
    protected $flags;

    /**
     * @var string
     */
    protected $compiledPattern;

    public function __construct(string $pattern, array $flags = [], string $name = '')
    {
        parent::__construct($name);
        $this->pattern = Formatter::removeComments($pattern);
        $this->flags = array_unique(array_filter($flags));

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
            implode('', array_unique(array_merge($this->flags, ['S', 'x'])))
        );
    }
}
