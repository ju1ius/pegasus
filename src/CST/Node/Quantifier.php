<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\CST\Node;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class Quantifier extends Composite
{
    public $isQuantifier = true;

    public function __construct(string $name, int $start, int $end, array $children, bool $optional = false)
    {
        $this->name = $name;
        $this->start = $start;
        $this->end = $end;
        $this->children = $children;
        $this->isOptional = $optional;
    }
}
