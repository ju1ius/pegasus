<?php declare(strict_types=1);

namespace ju1ius\Pegasus\CST\Exception;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Debug\CSTDumper;
use ju1ius\Pegasus\Debug\Debug;

/**
 * Something went wrong while traversing a parse tree.
 */
class TransformException extends \RuntimeException
{
    /**
     * @param Node       $node     The node at which the error occurred
     * @param Node       $rootNode The root node of the parse tree.
     * @param string     $msg
     * @param \Exception $previous Optional exception to wrap with debug info.
     */
    public function __construct(Node $node, Node $rootNode, string $msg = '', \Exception $previous = null)
    {
        $msg = sprintf(
            "%s\n\nConcrete Syntax Tree:\n%s",
            $msg ?: ($previous ? $previous->getMessage() : ''),
            $this->printParseTree($node, $rootNode)
        );
        parent::__construct($msg, 0, $previous);
    }

    private function printParseTree(Node $node, Node $rootNode): string
    {
        $output = Debug::createBufferedOutput();
        CSTDumper::dump($rootNode, $output, $node);

        return $output->fetch();
    }
}
