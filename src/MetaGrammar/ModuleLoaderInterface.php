<?php declare(strict_types=1);

namespace ju1ius\Pegasus\MetaGrammar;

use ju1ius\Pegasus\Grammar;

interface ModuleLoaderInterface
{
    public function load(string $url): Grammar;
}
