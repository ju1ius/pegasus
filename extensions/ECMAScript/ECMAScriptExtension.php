<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Compiler\Extension\ECMAScript;

use ju1ius\Pegasus\Compiler\CompilerInterface;
use ju1ius\Pegasus\Compiler\Extension;

class ECMAScriptExtension extends Extension
{
    public function getName(): string
    {
        return 'ecmascript';
    }

    public function getLanguage(): string
    {
        return 'ecmascript';
    }

    public function getCompiler(): CompilerInterface
    {
        return new ECMAScriptCompiler();
    }
}
