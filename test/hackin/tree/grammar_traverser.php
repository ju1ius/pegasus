<?php
require_once __DIR__.'/utils.php';

use ju1ius\Pegasus\Visitor\GrammarTraverser;
use ju1ius\Pegasus\Debug\GrammarPrinter;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\MetaGrammar;


$g = clone MetaGrammar::getGrammar();
$trav = (new GrammarTraverser)
    ->addVisitor(new GrammarPrinter)
;
$trav->traverse($g);

echo $g, "\n";
assert($g !== MetaGrammar::getGrammar());
