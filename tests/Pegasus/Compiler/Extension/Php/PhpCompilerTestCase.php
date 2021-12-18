<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Compiler\Extension\Php;

use Composer\InstalledVersions;
use ju1ius\Pegasus\Compiler\Extension\Php\PhpCompiler;
use ju1ius\Pegasus\Compiler\Extension\Php\Runtime\Parser;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\OptimizationLevel;
use ju1ius\Pegasus\GrammarFactory;
use ju1ius\Pegasus\Tests\PegasusTestCase;

class PhpCompilerTestCase extends PegasusTestCase
{
    /**
     * @throws Grammar\Exception\MissingTraitAlias
     */
    protected function compile(Grammar|string $syntaxOrGrammar): Parser
    {
        if (\is_string($syntaxOrGrammar)) {
            $grammar = GrammarFactory::fromSyntax($syntaxOrGrammar, null, OptimizationLevel::NONE);
            $hash = spl_object_hash($grammar) . '_' . sha1($syntaxOrGrammar);
        } else {
            $grammar = $syntaxOrGrammar;
            $hash = spl_object_hash($grammar) . '_' . sha1((string)$grammar);
        }
        $name = $grammar->getName() ?: 'Grammar';
        $class = sprintf('%s_%s', $name, $hash);
        $args = [
            'class' => $class,
        ];

        $compiler = new PhpCompiler();
        $root = InstalledVersions::getRootPackage()['install_path'];
        $cacheDir = "{$root}/tmp/cache";
        //$compiler->useCache("{$cacheDir}/twig");
        $code = $compiler->compileGrammar($grammar, $args);
        $file = sprintf('%s/generated-parser.php', $cacheDir);
        file_put_contents($file, $code);

        try {
            include $file;
        } catch (\Throwable $err) {
            throw new \RuntimeException(
                "Error evaluating generated code:\n\n"
                . $code
                . "\n\n",
                $err->getCode(),
                $err,
            );
        }

        return new $class();
    }
}
