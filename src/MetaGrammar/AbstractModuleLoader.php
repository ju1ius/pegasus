<?php declare(strict_types=1);

namespace ju1ius\Pegasus\MetaGrammar;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\MetaGrammar\Exception\ImportError;
use ju1ius\Pegasus\Parser\LeftRecursivePackratParser;

abstract class AbstractModuleLoader implements ModuleLoaderInterface
{
    public function load(string $url): Grammar
    {
        $syntax = $this->fetchUrl($url);

        $metaGrammar = MetaGrammar::create();
        $tree = (new LeftRecursivePackratParser($metaGrammar))->parse($syntax);
        $transform = new MetaGrammarTransform();
        /** @var Grammar $grammar */
        $grammar = $transform->transform($tree);

        $imports = $this->resolveImports($url, $transform->getImports());

        if ($parent = $transform->getParent()) {
            if (empty($imports[$parent])) {
                throw new ImportError(sprintf(
                    'Parent grammar "%s" was not imported.',
                    $parent
                ));
            }
            $grammar->extends($imports[$parent]);
            unset($imports[$parent]);
        }

        foreach ($imports as $alias => $trait) {
            $grammar->use($trait, $alias);
        }

        return $grammar;
    }

    abstract protected function fetchUrl(string $url): string;

    abstract protected function resolveImport(string $baseUrl, string $url): Grammar;

    /**
     * @return Grammar[]
     */
    final protected function resolveImports(string $baseUrl, array $imports): array
    {
        foreach ($imports as $alias => $path) {
            $imports[$alias] = $this->resolveImport($baseUrl, $path);
        }
        return $imports;
    }
}
