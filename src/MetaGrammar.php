<?php declare(strict_types=1);

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Grammar\OptimizationLevel;
use ju1ius\Pegasus\Grammar\Optimizer;

/**
 * Factory class that builds a Grammar instance capable of parsing other grammars.
 */
final class MetaGrammar
{
    /**
     * The unique instance of the optimized meta grammar.
     */
    private static ?Grammar $instance = null;

    /**
     * Unique instance of the unoptimized grammar.
     */
    private static ?Grammar $grammar = null;

    private function __construct() {}

    /**
     * Returns the unique instance of the (optimized) MetaGrammar.
     */
    public static function create(): Grammar
    {
        return self::$instance ??= Optimizer::optimize(self::getGrammar(), OptimizationLevel::LEVEL_2);
    }

    /**
     * Returns the unique instance of the (unoptimized) MetaGrammar.
     * Useful for debugging.
     */
    public static function getGrammar(): Grammar
    {
        return self::$grammar ??= require __DIR__ . '/MetaGrammar/metagrammar.php';
    }
}
