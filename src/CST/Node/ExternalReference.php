<?php declare(strict_types=1);


namespace ju1ius\Pegasus\CST\Node;


use ju1ius\Pegasus\CST\Node;


class ExternalReference extends Node
{
    public string $namespace;
    public ?Node $child;

    public function __construct(string $namespace, string $name, int $start, int $end, ?Node $child = null)
    {
        $this->namespace = $namespace;
        $this->name = $name;
        $this->start = $start;
        $this->end = $end;
        $this->child = $child;
    }
}
