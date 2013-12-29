<?php

require_once __DIR__.'/../../Pegasus_TestCase.php';


class ExpressionBase_TestCase extends Pegasus_TestCase
{
    protected function expr($class, array $args)
    {
        $class = "ju1ius\Pegasus\Expression\\$class";
        return (new \ReflectionClass($class))->newInstanceArgs($args);
    }
}
