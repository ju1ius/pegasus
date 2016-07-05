<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Exception;

use ju1ius\Pegasus\Debug\ParseTreePrinter;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Traverser\NodeTraverser;

/**
 * Something went wrong while traversing a parse tree.
 *
 * This exception exists to augment an underlying exception with information
 * about where in the parse tree the error occurred. Otherwise, it could be
 * tiresome to figure out what went wrong; you'd have to play back the whole
 * tree traversal in your head.
 *
 * @todo Make this serializable
 */
class VisitationError extends \RuntimeException
{
    /**
     * @var Node
     */
    protected $node;

    /**
     * @param Node       $node The node at which the error occurred
     * @param string     $msg
     * @param \Exception $previous Optional exception to wrap with debug info.
     */
    public function __construct(Node $node, $msg = '', \Exception $previous = null)
    {
        $this->node = $node;
        parent::__construct($msg, 0, $previous);
    }

    /**
     * @return Node
     */
    public function getNode()
    {
        return $this->node;
    }

    public function printParseTree(Node $rootNode = null)
    {
        $traverser = (new NodeTraverser)
            ->addVisitor(new ParseTreePrinter($this->getNode()))
            ->traverse($rootNode ?: $this->getNode());
    }

    public function __toString()
    {
        $prev = $this->getPrevious();
        return sprintf(
            "%s: %s\n\nParse tree:\n%s\n",
            $prev ? get_class($this->getPrevious()) : __CLASS__,
            $prev ? (string) $this->getPrevious() : $this->getMessage(),
            $this->printParseTree()
        );
    }
}
