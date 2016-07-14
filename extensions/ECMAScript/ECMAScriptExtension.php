<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler\Extension\ECMAScript;

use ju1ius\Pegasus\Compiler\Extension;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class ECMAScriptExtension extends Extension
{
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'ecmascript';
    }

    /**
     * @inheritDoc
     */
    public function getLanguage()
    {
        return 'ecmascript';
    }

    /**
     * @inheritDoc
     */
    public function getCompiler()
    {
        return new ECMAScriptCompiler();
    }
}
