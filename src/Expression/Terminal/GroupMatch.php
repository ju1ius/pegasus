<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Terminal;

use ju1ius\Pegasus\CST\Node\Terminal as TerminalNode;
use ju1ius\Pegasus\Expression\TerminalExpression as TerminalExpression;
use ju1ius\Pegasus\Parser\Parser;

/**
 * This type of expression is generated by the optimizer,
 * as the result of combining multiple consecutive Match expressions
 * into one optimized regular expression.
 *
 * You should not use it directly.
 *
 * @internal
 */
final class GroupMatch extends TerminalExpression
{
    private string $compiledPattern;
    private string $compiledFlags;

    public function __construct(
        private RegExp $matcher,
        private int $groupCount,
        string $name = ''
    ) {
        $this->compiledPattern = $matcher->getCompiledPattern();
        $this->compiledFlags = $matcher->getCompiledFlags();
        parent::__construct($name);
    }

    public function getMatcher(): RegExp
    {
        return $this->matcher;
    }

    public function getPattern(): string
    {
        return $this->matcher->getPattern();
    }

    /**
     * @return string[]
     */
    public function getFlags(): array
    {
        return $this->matcher->getFlags();
    }

    public function getCompiledPattern(): string
    {
        return $this->compiledPattern;
    }

    public function getCompiledFlags(): string
    {
        return $this->compiledFlags;
    }

    public function getCaptureCount(): int
    {
        return $this->groupCount;
    }

    public function matches(string $text, Parser $parser): TerminalNode|bool
    {
        if (!mb_ereg_search_setpos($parser->pos)) return false;
        if ($pos = mb_ereg_search_pos($this->compiledPattern, $this->compiledFlags)) {
            [$start, $length] = $pos;
            $parser->pos += $length;
            if ($parser->isCapturing) {
                $match = mb_ereg_search_getregs();
                if ($this->groupCount === 1) {
                    return new TerminalNode($this->name, $start, $parser->pos, $match[1]);
                }
                return new TerminalNode($this->name, $start, $parser->pos, $match[0], [
                    'captures' => array_slice($match, 1)
                ]);
            }
            return true;
        }
        return false;
    }

    public function __toString(): string
    {
        return sprintf('GroupMatch[%s, %d]', $this->compiledPattern, $this->groupCount);
    }
}
