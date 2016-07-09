<?php
use ju1ius\Pegasus\Debug\ParseTreePrinter;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Traverser\NodeTraverser;

require_once __DIR__.'/../../vendor/autoload.php';

/**
 * Returns a string representation of current function's caller.
 */
function get_caller()
{
    $stack = debug_backtrace();
    $caller = $stack[2];
    return sprintf(
        '%s%s(%s)',
        $caller['type'] ? $caller['class'] . $caller['type'] : '',
        $caller['function'],
        implode(', ', array_map(function($arg) {
            if (is_object($arg)) {
                return get_class($arg);
            }
            return null === $arg ? 'NULL': (string) $arg;
        }, $caller['args']))
    );
}

function dump_node(Node $node)
{
    $t = new NodeTraverser();
    $t->addVisitor(new ParseTreePrinter(null, false));
    $t->traverse($node);
}

function debug()
{
    echo implode(' ', func_get_args()), "\n";
}
