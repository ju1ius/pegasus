<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler\Extension\Php;

use ju1ius\Pegasus\Compiler\Extension;


class PhpExtension extends Extension
{
    public function getName()
    {
        return 'php';
    }

    public function getLanguage()
    {
        return 'php';
    }

    public function getCompiler()
    {
        return new PhpCompiler();
    }
}
