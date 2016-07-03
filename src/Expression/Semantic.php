<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class Semantic extends Expression
{
    public function isSemantic()
    {
        return true;
    }
}
