<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar\Optimization;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Terminal\AbstractRegExp;
use LogicException;


interface RegExpManipulator
{
    /**
     * @throws LogicException if expression cannot be converted to a pattern.
     */
    public function patternFor(Expression $expr): string;

    public function hasUnmergeableFlags(AbstractRegExp $expr): bool;

    public function atomic(string $pattern): string;

    public function positiveLookahead(string $pattern): string;

    public function negativeLookahead(string $pattern): string;
}
