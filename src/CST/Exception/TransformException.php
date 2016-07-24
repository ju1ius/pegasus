<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\CST\Exception;

use ju1ius\Pegasus\Debug\Debug;
use ju1ius\Pegasus\Debug\CSTDumper;
use ju1ius\Pegasus\CST\Node;

/**
 * Something went wrong while traversing a parse tree.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class TransformException extends \RuntimeException
{
    /**
     * @param Node       $node     The node at which the error occurred
     * @param Node       $rootNode The root node of the parse tree.
     * @param string     $msg
     * @param \Exception $previous Optional exception to wrap with debug info.
     */
    public function __construct(Node $node, Node $rootNode, $msg = '', \Exception $previous = null)
    {
        $msg = sprintf(
            "%s\n\nConcrete Syntax Tree:\n%s",
            $msg ?: ($previous ? $previous->getMessage() : ''),
            $this->printParseTree($node, $rootNode)
        );
        parent::__construct($msg, 0, $previous);
    }

    /**
     * @param Node $node
     * @param Node $rootNode
     *
     * @return string
     */
    private function printParseTree(Node $node, Node $rootNode)
    {
        $output = Debug::createBufferedOutput();
        CSTDumper::dump($rootNode, $output, $node);

        return $output->fetch();
    }
}
