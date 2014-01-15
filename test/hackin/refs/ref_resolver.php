<?php

require_once __DIR__.'/../../../vendor/autoload.php';


use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Reference as Ref;
use ju1ius\Pegasus\Visitor\ExpressionTraverser;
use ju1ius\Pegasus\Visitor\ReferenceResolver;


$g = new Grammar();

$g['start'] = new Ref('foobars');
$g['foobars'] = new OneOf([

]);

$g->finalize();

foreach ($g as $name => $expr) {
	echo $expr->asRule(), "\n";
}
