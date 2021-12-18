<?php declare(strict_types=1);

namespace ju1ius\Pegasus\MetaGrammar;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\MetaGrammar\Exception\ImportError;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Parser\LeftRecursivePackratParser;
use Webmozart\PathUtil\Path;

class FileParser
{
    /**
     * @var Grammar[]
     */
    private array $cache = [];

    public function parse(string $path): Grammar
    {
        $path = Path::canonicalize($path);

        $syntax = file_get_contents($path);
        $metaGrammar = MetaGrammar::create();
        $tree = (new LeftRecursivePackratParser($metaGrammar))->parse($syntax);
        $transform = new MetaGrammarTransform();
        /** @var Grammar $grammar */
        $grammar = $transform->transform($tree);

        $imports = $this->resolveImports(Path::getDirectory($path), $transform->getImports());

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

    /**
     * @return Grammar[]
     */
    private function resolveImports(string $basePath, array $imports): array
    {
        foreach ($imports as $alias => $path) {
            $imports[$alias] = $this->resolveImport($basePath, $path);
        }

        return $imports;
    }

    private function resolveImport(string $basePath, string $path): Grammar
    {
        $path = Path::canonicalize($path);
        if (!Path::isAbsolute($path)) {
            $path = Path::join($basePath, $path);
        }

        return $this->cache[$path] ??= $this->parseImport($path);
    }

    private function parseImport(string $path): Grammar
    {
        if (!is_file($path)) {
            throw new ImportError(sprintf('Not a file: %s', $path));
        }
        if (!is_readable($path)) {
            throw new ImportError(sprintf('File not readable: %s', $path));
        }
        try {
            return $this->parse($path);
        } catch (ParseError $error) {
            throw new ImportError(sprintf(
                'Invalid grammar in "%s"',
                $path
            ), $error);
        }
    }
}
