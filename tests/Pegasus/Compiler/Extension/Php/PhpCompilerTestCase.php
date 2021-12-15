<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Tests\Compiler\Extension\Php;

use ju1ius\Pegasus\Compiler\Extension\Php\PhpCompiler;
use ju1ius\Pegasus\Compiler\Extension\Php\Runtime\Parser;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Tests\PegasusTestCase;

class PhpCompilerTestCase extends PegasusTestCase
{
    /**
     * @throws Grammar\Exception\MissingTraitAlias
     */
    protected function compile(Grammar|string $syntaxOrGrammar): Parser
    {
        if (\is_string($syntaxOrGrammar)) {
            $grammar = Grammar::fromSyntax($syntaxOrGrammar, null, 0);
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
        $code = $compiler->compileGrammar($grammar, $args);
        $this->evaluateCode($code);

        return new $class();
    }

    private function evaluateCode(string $code): void
    {
        $code = preg_replace('/^<\?php/', '', $code);
        eval($code);
    }
}
