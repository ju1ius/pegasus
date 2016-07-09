<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Optimization\Match;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
final class CompositeReducing
{
    public static function finishReducing($expr, $children)
    {
        if (count($children) === 1) {
            return $children[0];
        }

        $expr->children = $children;

        return $expr;
    }
}
