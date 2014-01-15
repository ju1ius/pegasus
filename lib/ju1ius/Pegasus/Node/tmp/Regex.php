<?php

namespace ju1ius\Pegasus\Node;

use ju1ius\Pegasus\Node\Terminal;


/**
 * Node returned from a ``Regex`` expression
 *
 * Grants access to the ``re.Match`` object, in case you want to access
 * capturing groups, etc. 
 *
 */
class Regex extends Terminal
{
    /**
     * @var array Array of regex matches, as returned preg_match.
     */
    public $matches;

    public function __construct($expr_name, $full_text, $start, $end, $matches=[])
    {
        parent::__construct($expr_name, $full_text, $start, $end, $matches);
        $this->matches = $matches;
    }

    public function __toString()
    {
        return $this->matches[0];
    }
}
