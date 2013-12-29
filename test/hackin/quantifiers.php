<?php

require_once __DIR__.'/../../vendor/autoload.php';

use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Node;


$q = new Quantifier([new Literal('x')], 'q', 1, 3);
echo $q . PHP_EOL;

$node = $q->match('xx');

var_dump($node);
