<?php
require_once __DIR__.'/../utils.php';

use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Optimization\Analysis;


$g = MetaGrammar::getGrammar()->copy(true);
$g->unfold();

$a = new Analysis($g);

var_dump($a->isLeftRecursive('atom'));
