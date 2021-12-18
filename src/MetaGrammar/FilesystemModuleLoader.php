<?php declare(strict_types=1);

namespace ju1ius\Pegasus\MetaGrammar;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\MetaGrammar\Exception\ImportError;
use ju1ius\Pegasus\Parser\Exception\ParseError;
use Symfony\Component\Filesystem\Path;

final class FilesystemModuleLoader extends AbstractModuleLoader
{
    /**
     * @var Grammar[]
     */
    private array $cache = [];

    protected function fetchUrl(string $url): string
    {
        $url = Path::canonicalize($url);
        if (!is_file($url)) {
            throw new ImportError(sprintf('Not a file: %s', $url));
        }
        if (!is_readable($url)) {
            throw new ImportError(sprintf('File not readable: %s', $url));
        }
        return file_get_contents($url);
    }

    protected function resolveImport(string $baseUrl, string $url): Grammar
    {
        $url = Path::canonicalize($url);
        if (!Path::isAbsolute($url)) {
            $url = Path::join(Path::getDirectory($baseUrl), $url);
        }

        return $this->cache[$url] ??= $this->loadImport($url);
    }

    private function loadImport(string $url): Grammar
    {
        try {
            return $this->load($url);
        } catch (ParseError $error) {
            throw new ImportError(sprintf(
                'Invalid grammar in "%s"',
                $url
            ), $error);
        }
    }
}
