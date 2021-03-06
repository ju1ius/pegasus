<?php declare(strict_types=1);


namespace ju1ius\Pegasus\MetaGrammar;


use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\MetaGrammar\Exception\ImportError;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use ju1ius\Pegasus\Parser\LeftRecursivePackrat;
use Webmozart\PathUtil\Path;


class FileParser
{
    /**
     * @var Grammar[]
     */
    private $cache = [];

    public function parse(string $path): Grammar
    {
        $path = Path::canonicalize($path);

        $syntax = file_get_contents($path);
        $metaGrammar = MetaGrammar::create();
        $tree = (new LeftRecursivePackrat($metaGrammar))->parse($syntax);
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
     * @param string $basePath
     * @param string[] $imports
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
        if (!is_file($path)) {
            throw new ImportError(sprintf('Not a file: %s', $path));
        }
        if (!is_readable($path)) {
            throw new ImportError(sprintf('File not readable: %s', $path));
        }

        if (empty($this->cache[$path])) {
            try {
                $this->cache[$path] = $this->parse($path);
            } catch (ParseError $error) {
                throw new ImportError(sprintf(
                    'Invalid grammar in "%s"',
                    $path
                ), $error);
            }
        }

        return $this->cache[$path];
    }
}
