<?php
require_once __DIR__.'/../../../vendor/autoload.php';

use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Parser\LRPackrat as Parser;



$text = <<<'EOS'
rule = foo | bar

# disiz foo
foo = 'foo'

bar = 'bar'
EOS;

//$grammar = MetaGrammar::create();
//$parser = new Parser($grammar);
//$tree = $parser->parseAll($text);


$grammar = Grammar::fromSyntax($text);
$parser = new Parser($grammar);
$tree = $parser->parseAll('bar');

echo $tree->inspect(), "\n";
