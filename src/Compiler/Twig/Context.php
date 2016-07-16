<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler\Twig;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class Context extends \ArrayObject
{
    public function get($key, $default = null)
    {
        if (!$this->offsetExists($key)) {
            return $default;
        }

        return $this->offsetGet($key);
    }

    public function set($key, $value)
    {
        $this->offsetSet($key, $value);

        return $value;
    }
}
