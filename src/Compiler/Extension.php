<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler;

use ju1ius\Pegasus\Compiler\Compiler;

/**
 * Class Extension
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class Extension
{
    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @return string
     */
    abstract public function getLanguage();

    /**
     * @return Compiler
     */
    abstract public function getCompiler();
}
