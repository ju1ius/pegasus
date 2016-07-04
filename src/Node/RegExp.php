<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Node;

/**
 * Node returned from a `RegExp` expression.
 *
 * Grants access to the matches array, in case you want to access capturing groups.
 */
class RegExp extends Terminal
{
    /**
     * @var array Array of regex matches, as returned by `preg_match`.
     */
    public $matches;

    public function __construct($name, $fullText, $start, $end, $matches = [])
    {
        parent::__construct($name, $fullText, $start, $end, $matches);
        $this->matches = $matches;
    }

    public function __toString()
    {
        return $this->matches[0];
    }
}
