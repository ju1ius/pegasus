<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression\Terminal;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class HexDigit extends Match
{
    /**
     * @inheritDoc
     */
    public function __construct($name = '')
    {
        parent::__construct('[\da-fA-F]', [], $name);
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return 'XDIGIT';
    }
}
