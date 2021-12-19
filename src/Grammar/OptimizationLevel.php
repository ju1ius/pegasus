<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Grammar;

enum OptimizationLevel: int
{
    case NONE = 0;
    /**
     * Enables only transparent optimizations,
     * i.e. parsing a grammar and echoing it right away should yield no visual differences.
     */
    case LEVEL_1 = 1;
    /**
     * Enables more aggressive optimizations,
     * i.e. joining regexps, inlining rules, etc.
     */
    case LEVEL_2 = 2;
    /**
     * Highest level, use only with compiler.,
     */
    case LEVEL_3 = 3;
}
