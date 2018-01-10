<?php declare(strict_types=1);
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
final class Digit extends Match
{
    /**
     * @inheritDoc
     */
    public function __construct(string $name = '')
    {
        parent::__construct('\d', [], $name);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return 'DIGIT';
    }
}
