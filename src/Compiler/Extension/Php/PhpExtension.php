<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler\Extension\Php;

use ju1ius\Pegasus\Compiler\CompilerInterface;
use ju1ius\Pegasus\Compiler\Extension;


class PhpExtension extends Extension
{
    public function getName(): string
    {
        return 'php';
    }

    public function getLanguage(): string
    {
        return 'php';
    }

    public function getCompiler(): CompilerInterface
    {
        return new PhpCompiler();
    }
}
