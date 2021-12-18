<?php declare(strict_types=1);

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Grammar\Exception\AnonymousTopLevelExpression;
use ju1ius\Pegasus\Grammar\Exception\MissingTraitAlias;
use ju1ius\Pegasus\Grammar\Exception\RuleNotFound;
use ju1ius\Pegasus\Grammar\OptimizationLevel;
use ju1ius\Pegasus\Grammar\Optimizer;
use ju1ius\Pegasus\MetaGrammar\FilesystemModuleLoader;
use ju1ius\Pegasus\MetaGrammar\MetaGrammarTransform;
use ju1ius\Pegasus\Parser\LeftRecursivePackratParser;

final class GrammarFactory
{
    /**
     * Factory method that constructs a Grammar object from an associative array of rules.
     *
     * @param array<string, Expression> $rules
     * @param ?string $startRule
     * @param OptimizationLevel $optimizationLevel
     * @throws MissingTraitAlias
     * @throws RuleNotFound
     */
    public static function fromArray(
        array $rules,
        ?string $startRule = null,
        OptimizationLevel $optimizationLevel = OptimizationLevel::NONE,
    ): Grammar {
        $grammar = new Grammar();
        foreach ($rules as $name => $rule) {
            $grammar[$name] = $rule;
        }
        if ($startRule) {
            $grammar->setStartRule($startRule);
        }

        return Optimizer::optimize($grammar, $optimizationLevel);
    }

    /**
     * Factory method that constructs a Grammar object from an Expression.
     * @throws AnonymousTopLevelExpression If no named start rule could be determined.
     * @throws MissingTraitAlias
     */
    public static function fromExpression(
        Expression $expr,
        ?string $startRule = null,
        OptimizationLevel $optimizationLevel = OptimizationLevel::NONE,
    ): Grammar {
        if (!$startRule) {
            if (!$expr->getName()) {
                throw new AnonymousTopLevelExpression($expr);
            }
            $startRule = $expr->getName();
        }

        $grammar = new Grammar();
        $grammar[$startRule] = $expr;

        return Optimizer::optimize($grammar, $optimizationLevel);
    }

    /**
     * Factory method that constructs a Grammar object from a grammar file.
     * @throws MissingTraitAlias
     */
    public static function fromFile(
        string $path,
        OptimizationLevel $optimizationLevel = OptimizationLevel::LEVEL_1,
    ): Grammar {
        $grammar = (new FilesystemModuleLoader())->load($path);

        return Optimizer::optimize($grammar, $optimizationLevel);
    }

    /**
     * Factory method that constructs a Grammar object from a syntax string.
     * @throws MissingTraitAlias
     */
    public static function fromSyntax(
        string $syntax,
        ?string $startRule = null,
        OptimizationLevel $optimizationLevel = OptimizationLevel::LEVEL_1,
    ): Grammar {
        $metaGrammar = MetaGrammar::create();
        $tree = (new LeftRecursivePackratParser($metaGrammar))->parse($syntax);
        $grammar = (new MetaGrammarTransform)->transform($tree);
        if ($startRule) {
            $grammar->setStartRule($startRule);
        }

        return Optimizer::optimize($grammar, $optimizationLevel);
    }
}
