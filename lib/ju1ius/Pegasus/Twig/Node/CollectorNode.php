<?php

namespace ju1ius\Pegasus\Twig\Node;

use Twig_Node;
use Twig_NodeInterface;
use Twig_Compiler;


class CollectorNode extends Twig_Node
{
    public function __construct($key, Twig_NodeInterface $body, $lineno, $tag = 'collect')
    {
        parent::__construct(['body' => $body], ['key' => $key], $lineno, $tag);
    }

    public function compile(Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("ob_start();\n")
            ->subcompile($this->getNode('body'))
            ->write('$context["data_collector"]->collect(')
            ->repr($this->getAttribute('key'))
            ->raw(", ob_get_clean());\n")
        ;
    }
}
