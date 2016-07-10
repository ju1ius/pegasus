<?php

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
