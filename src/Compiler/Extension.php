<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler;

/**
 * Class Extension
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class Extension
{
    abstract public function getName(): string;

    abstract public function getLanguage(): string;

    abstract public function getCompiler(): CompilerInterface;
}
