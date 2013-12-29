<?php

namespace ju1ius\Pegasus\Node;

use ju1ius\Pegasus\Node;

/**
 * Node returned from a ``Regex`` expression
 *
 * Grants access to the ``re.Match`` object, in case you want to access
 * capturing groups, etc. 
 *
 */
class Regex extends Node
{
    /**
     * @var array $match Array of regex matches, as returned by
     * preg_match with PREG_OFFSET_CAPTURE
     */
    public $match;

    public function __construct($expr_name, $full_text, $start, $end, $children=[], $match=[])
    {
        parent::__construct($expr_name, $full_text, $start, $end, $children);
        $this->match = $match;
    }
    public function __toString()
    {
        return $this->match[0];
    }
}
