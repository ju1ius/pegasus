<?php

namespace ju1ius\Pegasus\Exception;

use ju1ius\Pegasus\Node;


/**
 * Something went wrong while traversing a parse tree.
 *
 * This exception exists to augment an underlying exception with information
 * about where in the parse tree the error occurred. Otherwise, it could be
 * tiresome to figure out what went wrong; you'd have to play back the whole
 * tree traversal in your head.
 *
 * @todo Make this serializable
 **/
class VisitationError extends \RuntimeException
{
    /**
     * @param $exc  What went wrong. We wrap this and add more info. 
     * @param $node The node at which the error occurred 
     **/
    public function __construct(\Exception $exc, Node $node)
    {
        parent::__construct('', 0, $exc);
        $this->node = $node;
    }

    public function __toString()
    {
        return sprintf(
            "%s: %s\n\nParse tree:\n%s\n",
            get_class($this->getPrevious()),
            (string) $this->getPrevious(),
            $this->node->inspect($this->node)
        );
    }
    
}
