<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Grammar\Optimizer;

/**
 * Factory class that builds a Grammar instance capable of parsing other grammars.
 *
 * @author ju1ius
 */
final class MetaGrammar
{
    /**
     * @var Grammar The unique instance of the optimized meta grammar.
     */
    private static $instance = null;

    /**
     * @var Grammar Unique instance of the unoptimized grammar.
     */
    private static $grammar = null;

    /**
     * Private constructor.
     *
     * You can't instanciate MetaGrammar.
     * You just call MetaGrammar::create() and it returns an unique instance of Grammar.
     */
    private function __construct() {}

    /**
     * Factory method for MetaGrammar.
     *
     * @return Grammar
     */
    public static function create()
    {
        if (null === self::$instance) {
            $grammar = self::getGrammar();
            self::$instance = Optimizer::optimize($grammar, Optimizer::LEVEL_2);
        }

        return self::$instance;
    }

    /**
     * Returns the unique instance of the base grammar used to parse the MetaGrammar syntax.
     *
     * @return Grammar
     */
    public static function getGrammar()
    {
        if (null === self::$grammar) {
            self::$grammar = require __DIR__ . '/MetaGrammar/metagrammar.php';
        }

        return self::$grammar;
    }
}
