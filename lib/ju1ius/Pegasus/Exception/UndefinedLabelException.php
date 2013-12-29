<?php

namespace ju1ius\Pegasus\Exception;


/**
 * A rule referenced in a grammar was never defined.
 *
 * Circular references and forward references are okay,
 * but you have to define stuff at some point...
 */
class UndefinedLabelException extends \RuntimeException
{
    public function __construct($label)
    {
        $this->label = $label;
        parent::__construct("The label '{$this->label}' was never defined.");
    }
}
